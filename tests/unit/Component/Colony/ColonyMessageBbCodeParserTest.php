<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\StuTestCase;

final class ColonyMessageBbCodeParserTest extends StuTestCase
{
    #[DataProvider('images')]
    public function testImagesRemainInertOrAttributeSafe(string $body, bool $valid): void
    {
        $input = '[img]' . $body . '[/img]';
        $expected = $valid
            ? '<img src="' . htmlspecialchars($body, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '" style="max-height: 100%;max-width:100%;" />'
            : htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $parser = new ColonyMessageBbCodeParser();
        self::assertSame($expected, $parser->parse($input)->getAsHTML());
        self::assertSame('<strong>Nachricht</strong>' . $expected, $parser->parse('[b]Nachricht[/b]' . $input)->getAsHTML());
    }

    public static function images(): iterable
    {
        yield ['x" onerror="window.test=1', false];
        yield ['https://example.test/x" onerror="window.test=1', false];
        yield ['[b]https://example.test/image.png[/b]', false];
        yield ['[img]https://example.test/image.png[/img]', false];
        yield ['javascript:window.test=1', false];
        yield ['file:///tmp/image.png', false];
        yield ['data:image/svg+xml,test', false];
        yield ['https://example.test/image.png?a=1&b=2', true];
        yield ["https://example.test/player's-image.png", true];
        yield ['http://127.0.0.1/image.png', true];
    }
}
