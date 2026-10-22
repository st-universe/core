<?php

declare(strict_types=1);

namespace Stu\Lib;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Stu\StuTestCase;

final class StuBbCodeWithImageDefinitionSetTest extends StuTestCase
{
    #[DataProvider('invalidImageBodies')]
    public function testInvalidImagesRenderAsEscapedText(string $body): void
    {
        $input = '[img]' . $body . '[/img]';

        self::assertSame(
            htmlspecialchars($input, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $this->createParser()->parse($input)->getAsHTML()
        );
    }

    public static function invalidImageBodies(): iterable
    {
        yield ['x" onerror="alert(document.domain)'];
        yield ['https://example.test/x" onerror="alert(1)'];
        yield ['[b]https://example.test/image.png[/b]'];
        yield ['[img]https://example.test/image.png[/img]'];
        yield ['<img src=x onerror=alert(1)>'];
        yield ['javascript:alert(1)'];
        yield ['data:image/svg+xml,<svg onload=alert(1)>'];
        yield ['file:///tmp/test.png'];
        yield ['ftp://example.test/image.png'];
        yield ['//example.test/image.png'];
        yield ['/image.png'];
        yield ['https://'];
        yield [''];
        yield ["https://example.test/image.png\n"];
        yield ["https://example.test/\xFF"];
    }

    #[DataProvider('validImageUrls')]
    public function testValidImagesRemainAttributeSafe(string $url): void
    {
        self::assertSame(
            '<img src="' . htmlspecialchars($url, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')
                . '" style="max-height: 100%;max-width:100%;" />',
            $this->createParser()->parse('[img]' . $url . '[/img]')->getAsHTML()
        );
    }

    public static function validImageUrls(): iterable
    {
        yield ['http://example.test/image.png'];
        yield ['https://example.test/image.jpg'];
        yield ['HTTPS://example.test/image.webp'];
        yield ['https://example.test/image?size=large&version=2'];
        yield ["https://example.test/player's-image.png"];
        yield ['https://example.test/%22%20onerror%3D%22alert(1)'];
        yield ['https://example.test/[b]image[/b].png'];
        yield ['https://example.test/image?name={param}&option={option}'];
        yield ['https://example.test/image?name=&#34;onerror=alert(1)'];
    }

    public function testOtherFormattingStillWorksAroundImages(): void
    {
        self::assertSame(
            '<strong>Bild</strong><em>Text</em><u>Linie</u><span style="color: #123456">Farbe</span>'
                . '<img src="https://example.test/image.png" style="max-height: 100%;max-width:100%;" />',
            $this->createParser()->parse(
                '[b]Bild[/b][i]Text[/i][u]Linie[/u][color=#123456]Farbe[/color]'
                    . '[img]https://example.test/image.png[/img]'
            )->getAsHTML()
        );
    }

    public function testParserCanRenderMultipleImagesAndBeReused(): void
    {
        $parser = $this->createParser();
        $input = '[img]https://example.test/one.png[/img][img]x" onerror="alert(1)[/img]';
        $expected = '<img src="https://example.test/one.png" style="max-height: 100%;max-width:100%;" />'
            . '[img]x&quot; onerror=&quot;alert(1)[/img]';

        self::assertSame($expected, $parser->parse($input)->getAsHTML());
        self::assertSame($expected, $parser->parse($input)->getAsHTML());
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testRenderingDoesNotOpenHttpStreams(): void
    {
        self::assertTrue(stream_wrapper_unregister('http'));
        self::assertTrue(stream_wrapper_unregister('https'));
        self::assertTrue(stream_wrapper_register('http', BbCodeImageStream::class));
        self::assertTrue(stream_wrapper_register('https', BbCodeImageStream::class));

        try {
            foreach (['http://example.test/image.png', 'https://example.test/image.jpg'] as $url) {
                self::assertStringContainsString('<img src="' . $url . '"', $this->createParser()->parse('[img]' . $url . '[/img]')->getAsHTML());
            }
            self::assertSame(0, BbCodeImageStream::$opens);
        } finally {
            stream_wrapper_restore('http');
            stream_wrapper_restore('https');
        }
    }

    private function createParser(): ParserWithImageInterface
    {
        $services = require __DIR__ . '/../../../src/Config/services.php';

        return $services[ParserWithImageInterface::class]();
    }
}

final class BbCodeImageStream
{
    public mixed $context;

    public static int $opens = 0;

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        self::$opens++;

        return false;
    }
}
