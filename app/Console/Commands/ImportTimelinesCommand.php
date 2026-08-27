<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Exceptions\Timeline\TimelineParseException;
use App\Repositories\TimelineRepository;
use App\Services\TimelineParserService;
use Illuminate\Console\Command;

/**
 * Import timeline txt files from a histories directory.
 *
 * The directory must contain one sub-folder per locale (en, sr)
 * with one txt file per timeline. The import is idempotent:
 * timelines are upserted keyed on (locale, slug) and figures
 * are replaced on every run. With --prune, timelines whose
 * source file no longer exists are deleted afterwards.
 */
class ImportTimelinesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timelines:import
        {path : Path to the histories directory}
        {--prune : Delete timelines whose source txt file no longer exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import timeline txt files from the given histories directory';

    /**
     * Execute the console command.
     */
    public function handle(
        TimelineParserService $timelineParserService,
        TimelineRepository $timelineRepository
    ): int {
        $path = (string) $this->argument('path');

        $imported = 0;
        $seen = [];
        $unmapped = [];
        $filesByLocale = [];

        // Validate every locale folder up front so a mistyped path or a
        // missing folder never leads to a partial import or, worse, a
        // prune that wipes a whole locale.
        foreach ((array) config('timelines.locales') as $locale) {
            $dir = "{$path}/{$locale}";

            if (! is_dir($dir)) {
                $this->error("Directory not found: {$dir}");

                return self::FAILURE;
            }

            $files = glob("{$dir}/*.txt") ?: [];

            if ($files === []) {
                $this->error("No txt files found in {$dir}");

                return self::FAILURE;
            }

            $filesByLocale[$locale] = $files;
        }

        foreach ($filesByLocale as $locale => $files) {
            $seen[$locale] = [];

            foreach ($files as $file) {
                $source = basename($file);

                try {
                    $parsed = $timelineParserService->parse(
                        (string) file_get_contents($file),
                        $locale,
                        $source
                    );
                } catch (TimelineParseException $e) {
                    $this->error($e->getMessage());

                    return self::FAILURE;
                }

                $slug = basename($file, '.txt');

                [$translationKey, $mapped] = $this->translationKey($locale, $slug);

                if (! $mapped) {
                    $unmapped[] = "{$locale}/{$slug}";
                }

                $timelineRepository->upsertWithFigures([
                    'locale' => $locale,
                    'slug' => $slug,
                    'title' => $parsed['title'],
                    'tldr' => $parsed['tldr'],
                    'part_one' => $parsed['part_one'],
                    'part_two' => $parsed['part_two'],
                    'translation_key' => $translationKey,
                ], $parsed['figures'] ?? []);

                $seen[$locale][] = $slug;
                $imported++;
            }
        }

        if ((bool) $this->option('prune')) {
            $pruned = $timelineRepository->pruneExcept($seen);

            $this->info("Pruned {$pruned} timelines.");
        }

        if ($unmapped !== []) {
            $this->warn(
                'Slugs without a translation pair in config/timelines.php: '
                .implode(', ', $unmapped)
            );
        }

        $this->info("Imported {$imported} timelines.");

        return self::SUCCESS;
    }

    /**
     * Get the translation key for a timeline and whether the slug
     * is present in the configured pair map.
     *
     * Mapped EN/SR pairs share the EN slug as key; unmapped slugs
     * fall back to their own slug.
     *
     * @return array{0: string, 1: bool}
     */
    private function translationKey(string $locale, string $slug): array
    {
        $pairs = (array) config('timelines.pairs');

        if ($locale === 'en') {
            return [$slug, array_key_exists($slug, $pairs)];
        }

        $enSlug = array_search($slug, $pairs, true);

        if ($enSlug === false) {
            return [$slug, false];
        }

        return [(string) $enSlug, true];
    }
}
