<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Console\Commands\ImportTimelinesCommand;
use App\Models\Timeline;
use App\Repositories\TimelineRepository;
use App\Services\TimelineParserService;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Mockery;
use PHPUnit\Framework\TestCase;

class ImportTimelinesCommandStub extends ImportTimelinesCommand
{
    public string $path = '';

    public bool $prune = false;

    public array $errorMessages = [];

    public array $infoMessages = [];

    public array $warnMessages = [];

    public function argument($key = null)
    {
        return $this->path;
    }

    public function option($key = null)
    {
        return $this->prune;
    }

    public function error($string, $verbosity = null): void
    {
        $this->errorMessages[] = $string;
    }

    public function info($string, $verbosity = null): void
    {
        $this->infoMessages[] = $string;
    }

    public function warn($string, $verbosity = null): void
    {
        $this->warnMessages[] = $string;
    }
}

class ImportTimelinesCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $c = new Container;
        Container::setInstance($c);

        $c->singleton('config', fn (): Repository => new Repository([
            'timelines' => require __DIR__.'/../../config/timelines.php',
        ]));
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_imports_all_fixture_files_with_pair_linking(): void
    {
        $calls = [];

        $repo = Mockery::mock(TimelineRepository::class);
        $repo->shouldNotReceive('pruneExcept');
        $repo->shouldReceive('upsertWithFigures')
            ->times(5)
            ->andReturnUsing(function (array $attributes, array $figures) use (&$calls) {
                $calls[] = ['attributes' => $attributes, 'figures' => $figures];

                return new Timeline;
            });

        $command = $this->command();

        $result = $command->handle(new TimelineParserService, $repo);

        $this->assertSame(0, $result);
        $this->assertSame(['Imported 5 timelines.'], $command->infoMessages);
        $this->assertSame([], $command->errorMessages);

        $this->assertSame(
            [
                ['en', 'american-revolution-fails'],
                ['en', 'karadjordje-survives-assassination'],
                ['en', 'petrov-crisis-starts-nuclear-war'],
                ['sr', 'karadjordje-prezivljava-atentat'],
                ['sr', 'nepoznata-linija'],
            ],
            array_map(fn (array $call): array => [
                $call['attributes']['locale'],
                $call['attributes']['slug'],
            ], $calls)
        );

        // EN file parsed in full.
        $full = $calls[1]['attributes'];
        $this->assertSame('Karadjordje Survives Assassination', $full['title']);
        $this->assertNotNull($full['tldr']);
        $this->assertCount(2, $full['part_one']);
        $this->assertCount(2, $full['part_two']);
        $this->assertCount(4, $calls[1]['figures']);

        // Figures default to an empty list when the section is absent.
        $this->assertSame([], $calls[2]['figures']);

        // Mapped EN/SR pair shares the EN slug as translation key.
        $this->assertSame(
            'karadjordje-survives-assassination',
            $calls[1]['attributes']['translation_key']
        );
        $this->assertSame(
            'karadjordje-survives-assassination',
            $calls[3]['attributes']['translation_key']
        );

        // Unmapped SR slug falls back to its own slug and is reported.
        $this->assertSame(
            'nepoznata-linija',
            $calls[4]['attributes']['translation_key']
        );
        $this->assertCount(1, $command->warnMessages);
        $this->assertStringContainsString('sr/nepoznata-linija', $command->warnMessages[0]);
    }

    public function test_re_import_upserts_with_identical_attributes(): void
    {
        $firstRun = [];
        $secondRun = [];

        foreach ([&$firstRun, &$secondRun] as &$calls) {
            $repo = Mockery::mock(TimelineRepository::class);
            $repo->shouldReceive('upsertWithFigures')
                ->times(5)
                ->andReturnUsing(function (array $attributes, array $figures) use (&$calls) {
                    $calls[] = ['attributes' => $attributes, 'figures' => $figures];

                    return new Timeline;
                });

            $command = $this->command();

            $this->assertSame(0, $command->handle(new TimelineParserService, $repo));
        }

        // Idempotency: both runs upsert the same (locale, slug) keys
        // with identical payloads.
        $this->assertSame($firstRun, $secondRun);
    }

    public function test_prune_option_deletes_missing_timelines(): void
    {
        $repo = Mockery::mock(TimelineRepository::class);
        $repo->shouldReceive('upsertWithFigures')
            ->times(5)
            ->andReturn(new Timeline);
        $repo->shouldReceive('pruneExcept')
            ->once()
            ->with([
                'en' => [
                    'american-revolution-fails',
                    'karadjordje-survives-assassination',
                    'petrov-crisis-starts-nuclear-war',
                ],
                'sr' => [
                    'karadjordje-prezivljava-atentat',
                    'nepoznata-linija',
                ],
            ])
            ->andReturn(2);

        $command = $this->command();
        $command->prune = true;

        $result = $command->handle(new TimelineParserService, $repo);

        $this->assertSame(0, $result);
        $this->assertSame(
            ['Pruned 2 timelines.', 'Imported 5 timelines.'],
            $command->infoMessages
        );
    }

    public function test_aborts_with_failure_when_locale_directory_is_missing(): void
    {
        $repo = Mockery::mock(TimelineRepository::class);
        $repo->shouldNotReceive('upsertWithFigures');
        $repo->shouldNotReceive('pruneExcept');

        $command = $this->command(__DIR__.'/../Fixtures/does-not-exist');
        $command->prune = true;

        $result = $command->handle(new TimelineParserService, $repo);

        $this->assertSame(1, $result);
        $this->assertCount(1, $command->errorMessages);
        $this->assertStringContainsString('Directory not found', $command->errorMessages[0]);
        $this->assertSame([], $command->infoMessages);
    }

    public function test_aborts_with_failure_when_locale_directory_is_empty(): void
    {
        $repo = Mockery::mock(TimelineRepository::class);
        $repo->shouldNotReceive('upsertWithFigures');
        $repo->shouldNotReceive('pruneExcept');

        $command = $this->command(__DIR__.'/../Fixtures/empty');
        $command->prune = true;

        $result = $command->handle(new TimelineParserService, $repo);

        $this->assertSame(1, $result);
        $this->assertCount(1, $command->errorMessages);
        $this->assertStringContainsString('No txt files found', $command->errorMessages[0]);
        $this->assertSame([], $command->infoMessages);
    }

    public function test_aborts_with_failure_on_malformed_file(): void
    {
        $repo = Mockery::mock(TimelineRepository::class);
        $repo->shouldNotReceive('upsertWithFigures');

        $command = $this->command(__DIR__.'/../Fixtures/invalid');

        $result = $command->handle(new TimelineParserService, $repo);

        $this->assertSame(1, $result);
        $this->assertCount(1, $command->errorMessages);
        $this->assertStringContainsString('empty-section.txt', $command->errorMessages[0]);
        $this->assertSame([], $command->infoMessages);
    }

    private function command(?string $path = null): ImportTimelinesCommandStub
    {
        $command = new ImportTimelinesCommandStub;
        $command->path = $path ?? __DIR__.'/../Fixtures/histories';

        return $command;
    }
}
