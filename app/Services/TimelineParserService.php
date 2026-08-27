<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\Timeline\TimelineParseException;

/**
 * TimelineParserService parses alternate-history txt files.
 *
 * PHP port of the frontend parser (src/lib/parse.ts in the
 * alternate-history-wiki repo), storing one array element per
 * non-blank source line instead of 3-sentence blocks.
 */
class TimelineParserService
{
    /**
     * Parse the raw contents of a timeline txt file.
     *
     * @return array{title: string, tldr: string|null, figures: array<int, array{name: string, description: string}>|null, part_one: array<int, string>, part_two: array<int, string>}
     *
     * @throws TimelineParseException
     */
    public function parse(string $raw, string $locale, string $source): array
    {
        $markers = config("timelines.markers.{$locale}");

        throw_unless(
            is_array($markers),
            TimelineParseException::class,
            "{$source}: unsupported locale \"{$locale}\""
        );

        $lines = preg_split('/\r?\n/', $raw) ?: [];

        $title = $this->firstNonBlankLine($lines);

        throw_if(
            $title === null,
            TimelineParseException::class,
            "{$source}: empty file"
        );

        return [
            'title' => $title,
            'tldr' => $this->textAfter($lines, $markers['tldr']),
            'figures' => $this->figuresAfter($lines, $markers['figures']),
            'part_one' => $this->paragraphsAfter($lines, $markers['part_one'], $markers['all'], $source),
            'part_two' => $this->paragraphsAfter($lines, $markers['part_two'], $markers['all'], $source),
        ];
    }

    /**
     * Get the first non-blank line, trimmed.
     *
     * @param  array<int, string>  $lines
     */
    private function firstNonBlankLine(array $lines): ?string
    {
        foreach ($lines as $line) {
            $trimmed = mb_trim($line);

            if ($trimmed !== '') {
                return $trimmed;
            }
        }

        return null;
    }

    /**
     * Find the index of the line matching the given marker.
     *
     * @param  array<int, string>  $lines
     */
    private function markerIndex(array $lines, string $marker): ?int
    {
        foreach ($lines as $index => $line) {
            if (mb_trim($line) === $marker) {
                return $index;
            }
        }

        return null;
    }

    /**
     * Extract the required paragraph section after the given marker.
     *
     * @param  array<int, string>  $lines
     * @param  array<int, string>  $allMarkers
     * @return array<int, string>
     *
     * @throws TimelineParseException
     */
    private function paragraphsAfter(array $lines, string $marker, array $allMarkers, string $source): array
    {
        $index = $this->markerIndex($lines, $marker);

        throw_if(
            $index === null,
            TimelineParseException::class,
            "{$source}: missing marker \"{$marker}\""
        );

        $out = [];

        foreach (array_slice($lines, $index + 1) as $line) {
            $trimmed = mb_trim($line);

            if (in_array($trimmed, $allMarkers, true)) {
                break;
            }

            if ($trimmed !== '') {
                $out[] = $trimmed;
            }
        }

        throw_if(
            $out === [],
            TimelineParseException::class,
            "{$source}: no paragraph after \"{$marker}\""
        );

        return $out;
    }

    /**
     * Extract the optional text section after the given marker.
     *
     * @param  array<int, string>  $lines
     */
    private function textAfter(array $lines, string $marker): ?string
    {
        $index = $this->markerIndex($lines, $marker);

        if ($index === null) {
            return null;
        }

        $out = [];

        foreach (array_slice($lines, $index + 1) as $line) {
            $trimmed = mb_trim($line);

            if ($trimmed === '') {
                if ($out !== []) {
                    break;
                }

                continue;
            }

            $out[] = $trimmed;
        }

        return $out === [] ? null : implode(' ', $out);
    }

    /**
     * Extract the optional figures section after the given marker.
     *
     * Splits each line on the first space-dash-space
     * (hyphen, en-dash, or em-dash).
     *
     * @param  array<int, string>  $lines
     * @return array<int, array{name: string, description: string}>|null
     */
    private function figuresAfter(array $lines, string $marker): ?array
    {
        $index = $this->markerIndex($lines, $marker);

        if ($index === null) {
            return null;
        }

        $out = [];

        foreach (array_slice($lines, $index + 1) as $line) {
            $trimmed = mb_trim($line);

            if ($trimmed === '') {
                if ($out !== []) {
                    break;
                }

                continue;
            }

            $parts = preg_split('/ [-–—] /u', $trimmed, 2) ?: [$trimmed];

            $out[] = [
                'name' => $parts[0],
                'description' => $parts[1] ?? '',
            ];
        }

        return $out === [] ? null : $out;
    }
}
