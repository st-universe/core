<?php

declare(strict_types=1);

namespace Stu;

use Spatie\Snapshots\Exceptions\CantBeSerialized;

class LegacyWindowsHtmlDriverTest extends StuTestCase
{
    public function testSerializeKeepsHtmlUntouchedApartFromTrailingNewline(): void
    {
        $driver = new LegacyWindowsHtmlDriver();

        $html = "<!DOCTYPE html>\n"
            . "<html>\n"
            . "<body>\n"
            . "<picture><source media=\"(max-width: 768px)\"><img src=\"/assets/test.png\" title=\"Wałęsa\"></picture>\n"
            . "</body>\n"
            . "</html>";

        $result = $driver->serialize($html);

        $this->assertSame($html . "\n", $result);
    }

    public function testSerializeNormalizesWindowsLineEndings(): void
    {
        $driver = new LegacyWindowsHtmlDriver();

        $result = $driver->serialize("<div>\r\n\tTest\r\n</div>\r\n");

        $this->assertSame("<div>\n\tTest\n</div>\n", $result);
    }

    public function testSerializeReturnsSingleNewlineForEmptyString(): void
    {
        $driver = new LegacyWindowsHtmlDriver();

        $this->assertSame("\n", $driver->serialize(''));
    }

    public function testSerializeRejectsNonStrings(): void
    {
        $driver = new LegacyWindowsHtmlDriver();

        $this->expectException(CantBeSerialized::class);

        $driver->serialize(['not-a-string']);
    }
}
