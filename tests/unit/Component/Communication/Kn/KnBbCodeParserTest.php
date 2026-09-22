<?php

declare(strict_types=1);

namespace Stu\Component\Communication\Kn;

use Stu\StuTestCase;

class KnBbCodeParserTest extends StuTestCase
{
    public function testParseToHtmlEscapesAngleBrackets(): void
    {
        $parser = new KnBbCodeParser();

        $this->assertSame(
            'Vorher &lt;&lt; nachher &gt;&gt;',
            $parser->parseToHtml('Vorher << nachher >>')
        );
    }

    public function testParseToHtmlKeepsBbCode(): void
    {
        $parser = new KnBbCodeParser();

        $this->assertSame(
            'Vorher <strong>&lt;</strong> nachher',
            $parser->parseToHtml('Vorher [b]<[/b] nachher')
        );
    }
}
