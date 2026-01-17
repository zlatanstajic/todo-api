# Todo API

[![Tests](https://img.shields.io/badge/tests-PHPUnit-brightgreen)](./)
[![Coverage](https://img.shields.io/badge/coverage-html-blue)](storage/coverage/index.html)

This API is a simple Todo application demonstrating resource
controllers, service/repository layers, API resources, and auth.

## Table of Contents

* [Features](#features)
* [Todo Functionality](#todo-functionality)
  * [Todo API Endpoints](#todo-api-endpoints)
  * [Todo Model Fields](#todo-model-fields)
* [Getting Started](#getting-started)
* [Usage](#usage)
* [Postman Collection](#postman-collection)
* [API Documentation](#api-documentation)
* [Testing](#testing)
* [Contributing](#contributing)
* [License](#license)

---

## Features

- User authentication (via Sanctum)
- RESTful API for managing todos
- Service and repository layers for business logic and data access
- API resource formatting

### Prerequisites

- PHP 8.2+ and Composer 2.x
- Node.js if you will build frontend assets

### Quick start

```bash
git clone <repository-url>
cd todo-api
composer install
cp .env.example .env
php artisan key:generate
```

If you use frontend assets:

```bash
npm install
npm run build
```

### Database (local)

```bash
php artisan migrate:fresh --seed
```

Default local admin user (change before prod):
- Email: admin@example.com
- Password: admin

Note: `.extras/git/pre-commit` is installed during `composer install`.

[⬆ back to top](#table-of-contents)

---

## Getting Started

- **Clone the repository**

    ```bash
    git clone <repository-url>
    cd todo-api
    ```

- **Install Node.js dependencies**

    ```bash
    npm install
    ```

- **Install PHP dependencies**

    ```bash
    composer install
    ```

- **Copy environment file**

    ```bash
    cp .env.example .env
    ```

- **Generate application key**

    ```bash
    php artisan key:generate
    ```

- **Configure environment variables**

    - Edit `.env` to set your database and other settings.

- **Run database migrations and seeders**

    ```bash
    php artisan migrate:fresh --seed
    ```

    This will run all migrations and populate the database with initial data (users and todos).

    Alternatively, if you've already migrated:

    ```bash
    php artisan migrate
    php artisan db:seed
    ```

    **Default admin user:** The seeders create a default admin account for local development.
    - **Email:** `admin@example.com`
    - **Password:** `admin`

    Change this password immediately for any non-local environment.

    **Note:** The pre-commit hook from `.extras/git/pre-commit` is automatically installed during `composer install`, which runs code checks before every commit.

[⬆ back to top](#table-of-contents)

---

## Usage

- **Start the development server and assets watcher:**

    ```bash
    composer run dev
    ```

    This will:
    - Start the Laravel server
    - Start the queue listener
    - Start the log viewer (pail)
    - Start Vite for frontend assets

- Or, start servers individually:

    ```bash
    php artisan serve
    npm run dev
    ```

[⬆ back to top](#table-of-contents)

---

## Postman Collection

A ready-to-use Postman collection and environment are provided in the `.extras/postman` folder to help you quickly test the API endpoints.

- **Collection:**  
  [`todo-api.postman_collection.json`](.extras/postman/todo-api.postman_collection.json)  
  Contains requests for authentication, user info, and all Todo API operations (list, create, update, delete).

- **Environment:**  
  [`todo-api-development.postman_environment.json`](.extras/postman/todo-api-development.postman_environment.json)  
  Pre-configured with the base API URL and a placeholder for your access token.

[⬆ back to top](#table-of-contents)

---

## API Documentation

This project includes `dedoc/scramble` (a dev dependency) to generate OpenAPI-compatible API documentation automatically from the application code.

- **Routes added by Scramble:**
    - `/docs/api` — interactive UI viewer for the documentation
    - `/docs/api.json` — the OpenAPI JSON document describing the API

- **Notes:**
    - By default Scramble exposes these routes only in the `local` environment. To make them available in other environments, configure the `viewApiDocs` gate as documented in the [Scramble docs].
    - The JSON document is available at `http://<your-app>/docs/api.json` and can be used with tools that accept OpenAPI/Swagger JSON.

[⬆ back to top](#table-of-contents)

---


## Testing

**Useful commands**

```bash
# Run all checks (format, static analysis, tests)
composer run check

# Run tests with coverage
composer run phpunit

# Open HTML coverage report
open storage/coverage/index.html
```

[⬆ back to top](#table-of-contents)

## API Examples

Exchange credentials for a token:

```bash
curl -X POST http://localhost:8000/api/tokens \
    -H "Content-Type: application/json" \
    -d '{"email":"admin@example.com","password":"admin"}'
```

Use the returned bearer token to call protected endpoints:

```bash
curl http://localhost:8000/api/todos \
    -H "Authorization: Bearer <token>"
```

[⬆ back to top](#table-of-contents)

---

## Contributing

Contributions are welcome! Please open an issue or submit a pull request.

[⬆ back to top](#table-of-contents)

---

## License

This project is licensed under the MIT License. See [LICENSE](LICENSE) for details.

[⬆ back to top](#table-of-contents)

[Scramble docs]: https://scramble.dedoc.co/usage/getting-started#docs-authorization
