<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\Timeline\TimelineParseException;
use App\Services\TimelineParserService;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use PHPUnit\Framework\TestCase;

class TimelineParserServiceTest extends TestCase
{
    private TimelineParserService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $c = new Container;
        Container::setInstance($c);

        $c->singleton('config', fn (): Repository => new Repository([
            'timelines' => require __DIR__.'/../../config/timelines.php',
        ]));

        $this->service = new TimelineParserService;
    }

    public function test_parses_valid_en_file_with_all_sections(): void
    {
        $result = $this->service->parse($this->fixture('histories/en/karadjordje-survives-assassination.txt'), 'en', 'full.txt');

        $this->assertSame('Karadjordje Survives Assassination', $result['title']);
        $this->assertSame('The leader survives the attack. The rivalry never starts.', $result['tldr']);
        $this->assertSame([
            'The leader of the uprising was killed in his sleep in 1817.',
            'The rivalry between the two houses lasted for a century.',
        ], $result['part_one']);
        $this->assertSame([
            'The leader wakes up and fights off the attacker.',
            'The unified state expands earlier than recorded.',
        ], $result['part_two']);
        $this->assertSame([
            ['name' => 'First Person', 'description' => 'Described with a hyphen separator.'],
            ['name' => 'Second Person', 'description' => 'Described with an en dash separator.'],
            ['name' => 'Third Person', 'description' => 'Described with an em dash separator.'],
            ['name' => 'Fourth Person Without Description', 'description' => ''],
        ], $result['figures']);
    }

    public function test_parses_valid_sr_file_with_serbian_markers(): void
    {
        $result = $this->service->parse($this->fixture('histories/sr/karadjordje-prezivljava-atentat.txt'), 'sr', 'sr.txt');

        $this->assertSame('Karađorđe preživljava atentat', $result['title']);
        $this->assertSame('Vođa preživljava napad.', $result['tldr']);
        $this->assertSame(['Vođa ustanka je ubijen na spavanju 1817. godine.'], $result['part_one']);
        $this->assertSame(['Vođa se budi i savladava napadača.'], $result['part_two']);
        $this->assertSame([
            ['name' => 'Prva Ličnost', 'description' => 'Opisana crtom u ovoj liniji.'],
        ], $result['figures']);
    }

    public function test_parses_file_without_tldr(): void
    {
        $result = $this->service->parse($this->fixture('histories/en/american-revolution-fails.txt'), 'en', 'no-tldr.txt');

        $this->assertNull($result['tldr']);
        $this->assertSame([
            ['name' => 'Some General', 'description' => 'Lost the decisive battle in this timeline.'],
        ], $result['figures']);
    }

    public function test_parses_crlf_file_without_figures(): void
    {
        $result = $this->service->parse($this->fixture('histories/en/petrov-crisis-starts-nuclear-war.txt'), 'en', 'crlf.txt');

        $this->assertSame('Petrov Crisis Starts Nuclear War', $result['title']);
        $this->assertNull($result['tldr']);
        $this->assertNull($result['figures']);
        $this->assertSame(['The officer reported a false alarm in 1983.'], $result['part_one']);
        $this->assertSame(['The alarm is treated as a real attack.'], $result['part_two']);
    }

    public function test_throws_on_missing_part_one(): void
    {
        $this->expectException(TimelineParseException::class);
        $this->expectExceptionMessage('bad.txt: missing marker "PART ONE: RECORDED HISTORY"');

        $this->service->parse($this->fixture('invalid/en/missing-part-one.txt'), 'en', 'bad.txt');
    }

    public function test_throws_on_missing_part_two(): void
    {
        $this->expectException(TimelineParseException::class);
        $this->expectExceptionMessage('bad.txt: missing marker "PART TWO: ALTERNATE HISTORY"');

        $this->service->parse($this->fixture('invalid/en/missing-part-two.txt'), 'en', 'bad.txt');
    }

    public function test_throws_on_empty_section_after_marker(): void
    {
        $this->expectException(TimelineParseException::class);
        $this->expectExceptionMessage('bad.txt: no paragraph after "PART ONE: RECORDED HISTORY"');

        $this->service->parse($this->fixture('invalid/en/empty-section.txt'), 'en', 'bad.txt');
    }

    public function test_throws_on_empty_file(): void
    {
        $this->expectException(TimelineParseException::class);
        $this->expectExceptionMessage('bad.txt: empty file');

        $this->service->parse($this->fixture('invalid/en/empty.txt'), 'en', 'bad.txt');
    }

    public function test_throws_on_unsupported_locale(): void
    {
        $this->expectException(TimelineParseException::class);
        $this->expectExceptionMessage('bad.txt: unsupported locale "de"');

        $this->service->parse('Title', 'de', 'bad.txt');
    }

    private function fixture(string $path): string
    {
        return (string) file_get_contents(__DIR__.'/../Fixtures/'.$path);
    }
}
