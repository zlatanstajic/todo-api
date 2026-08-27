# Todo API Project Rules

Guidance for any coding agent working in this repository. Claude Code loads it through [`CLAUDE.md`](CLAUDE.md).

## Overview

Todo API is a Laravel 12 / PHP 8.2+ JSON API for personal todo items, authenticated with Laravel Sanctum personal access tokens. There is no meaningful frontend: `resources/views` holds a single welcome page, and `package.json` exists only to install the Husky pre-commit hook. Every todo belongs to exactly one user, and that ownership is enforced by a global Eloquent scope rather than by per-query `where` clauses.

## Commands

This is not a Sail or Docker project; run everything directly on the host.

```bash
composer test        # Full gate: rector (dry-run) → peck → pint --test → phpstan → phpunit --coverage --min=100
composer fix         # Auto-fix: rector process + pint (run before committing)
composer serve       # Runs server + queue listener + pail logs concurrently
composer setup       # Fresh install: deps, key, migrate:fresh --seed, ide-helper, npm install, test
```

Individual gate steps are `composer test:rector`, `composer test:peck`, `composer test:pint`, `composer test:phpstan`, and `composer test:phpunit`. Run the smallest relevant test before finalizing:

```bash
php artisan test --compact tests/Unit/TodoServiceTest.php
php artisan test --compact --filter=test_name
```

## Coverage is 100%, not a target

- `composer test:phpunit` runs `php artisan test --coverage --min=100`. A single uncovered branch fails the gate, and the Husky pre-commit hook runs the same gate, so uncovered code cannot be committed without `--no-verify`.
- Every new conditional, `catch` block, or early return needs a test in the same change. Do not add speculative code paths "for later": they have to be covered now.
- Do not remove or weaken existing tests to make the gate pass, and do not lower `--min`.

## Ownership is a global scope, and it is silent when nobody is authenticated

- `Todo::booted()` adds `App\Models\Scopes\UserScope`, which appends `where user_id = Auth::id()` — **but only when `Auth::check()` is true**. With no authenticated user the scope adds nothing and every todo in the table is visible.
- That means ownership is not enforced at the repository or service layer at all. `TodoRepository` calls plain `Todo::all()`, `findOrFail()`, and `destroy()`; correctness depends entirely on the request being authenticated by `auth:sanctum`. Never expose a todo route outside that middleware group, and never call the repository from an unauthenticated context (console command, queued job, seeder) expecting it to be owner-scoped.
- `user_id` is assigned in `TodoController::store()` from `$request->user()->id` after validation. It is deliberately absent from the validation rules: do not add it, or a client could create todos for another user.
- `TodoSeeder` and `TodoFactory` bypass all of this — the factory assigns `user_id` with `numberBetween(1, 2)`, which only lines up because `UserSeeder` creates exactly two users. Change one and you must change the other.

## Layering: Controller to Service to Repository to Model

- Controllers validate input, delegate to a constructor-injected service, and shape the response. They never touch Eloquent.
- Services hold business logic and delegate persistence to a repository. Repositories are the only layer that talks to the model.
- `App\Http\Controllers\Controller` is an abstract base providing `successResponse()` (wraps the payload in `data`) and `errorResponse()` (wraps the message in `error`). Extend it; do not build ad-hoc `response()->json()` envelopes in a controller.
- API output goes through `App\Http\Resources\TodoResource`, which emits a fixed `type` / `id` / `attributes` shape. Adding a column to the todos table does not expose it: add it to the resource too.

## Error responses: only pass `getCode()` where every exception carries an HTTP status

- Controllers catch `Exception` and convert it with `errorResponse()`. The domain exceptions in `app/Exceptions` each set a real status in their constructor (`TodoNotFoundException` 404, `TodoDeleteFailedException` 409, `UserNotFoundException` 404, `UserInvalidCredentialsException` 401), so passing `$e->getCode()` is safe on those paths.
- `TodoController::index()` and `TodoController::store()` deliberately omit the code and fall back to 500. `store()` can throw `ValidationException`, whose `getCode()` is `0`, and `response()->json(..., 0)` throws. Do not "improve" those two calls by adding `$e->getCode()`.
- A new domain exception must extend `Exception`, set its message from a translation key, and pass a `Symfony\Component\HttpFoundation\Response` constant as the code.

## Unit tests are stub-based and do not boot the database

- `tests/Unit` extends `PHPUnit\Framework\TestCase` directly (not `Tests\TestCase`) and drives repositories through hand-written subclasses such as `TodoStub`, which override `all()`, `findOrFail()`, `create()`, and `destroy()` with static fixtures. There is no `RefreshDatabase`, no migration run, and no container by default.
- Follow that pattern for new unit tests rather than reaching for a database. Reset the stub's static properties in `setUp()`; leftover state leaks between tests.
- `tests/Feature` currently holds only the framework's example test. A genuine HTTP test needs `Tests\TestCase`, an authenticated Sanctum user, and its own database setup — adding one is fine, but do not convert the existing stub-based unit tests to it.

## User-facing strings live in translation files

- Response and exception messages resolve through `__('messages.*')`, with `resources/lang/en/messages.php` and `resources/lang/sr/messages.php`. Add every new key to **both** files; a missing Serbian key renders the raw key string.
- Peck spell-checks the codebase (`composer test:peck`). Project-specific vocabulary belongs in the `ignore.words` list in [`peck.json`](peck.json), not in an inline suppression.

## Conventions

- Every PHP file starts with `declare(strict_types=1);`. Rector's PHP sets and the Laravel set list run in the gate, so hand-written code that Rector would rewrite fails `composer test:rector`; run `composer fix` first.
- PHPStan runs at level 5 over `app` with Larastan. Do not add entries to `ignoreErrors`; fix the type instead.
- Formatting is Laravel Pint with the custom rules in [`pint.json`](pint.json). Classes and public methods carry short docblocks — match the surrounding density rather than stripping them.
- Routes live in `routes/api.php` under a `throttle:60,1` group, with a stricter `throttle:3,1` on `POST /api/tokens`. Named routes follow `resource.action`.
- The default database is SQLite at `database/database.sqlite`, created by `composer setup`. `composer setup` runs `migrate:fresh` and destroys existing local data.
- Never read or write `.env`, and never commit credentials. The seeded admin account is configured through `ADMIN_NAME`, `ADMIN_EMAIL`, and `ADMIN_PASSWORD`.
- Do not run `git add` or `git commit` on your own initiative.

## Generated assets

- [`assets/img/og-image.png`](assets/img/og-image.png) is produced by [`scripts/gen-og-image.py`](scripts/gen-og-image.py) (Pillow, deterministic output). Edit the script and rerun `python3 scripts/gen-og-image.py`; never hand-edit the PNG.
- `_ide_helper.php`, `_ide_helper_models.php`, and `.phpstorm.meta.php` are generated by `composer artisan:ide-helper` and are git-ignored.
