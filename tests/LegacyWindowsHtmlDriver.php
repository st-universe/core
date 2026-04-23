<?php

declare(strict_types=1);

namespace Stu;

use DOMDocument;
use PHPUnit\Framework\Assert;
use Spatie\Snapshots\Driver;
use Spatie\Snapshots\Exceptions\CantBeSerialized;

final class LegacyWindowsHtmlDriver implements Driver
{
    public function serialize($data): string
    {
        if (!is_string($data)) {
            throw new CantBeSerialized('Only strings can be serialized to html');
        }

        if ($data === '') {
            return "\n";
        }

        $domDocument = new DOMDocument('1.0');
        $domDocument->preserveWhiteSpace = false;
        $domDocument->formatOutput = true;

        @$domDocument->loadHTML($data, LIBXML_HTML_NODEFDTD); // to ignore HTML5 errors

        $htmlValue = $domDocument->saveHTML();
        $htmlValue = $this->normalizeRelevantHtmlEntities($htmlValue);

        // Keep the historic snapshot encoding from older Windows runs so
        // cross-platform snapshot updates do not rewrite thousands of lines.
        $htmlValue = mb_convert_encoding($htmlValue, 'UTF-8', 'ISO-8859-1');
        $htmlValue = $this->encodeLegacyWindowsHighChars($htmlValue);

        return $htmlValue;
    }

    public function extension(): string
    {
        return 'html';
    }

    public function match($expected, $actual)
    {
        Assert::assertEquals($expected, $this->serialize($actual));
    }

    private function normalizeRelevantHtmlEntities(string $htmlValue): string
    {
        return strtr($htmlValue, [
            '&auml;' => 'ä',
            '&ouml;' => 'ö',
            '&uuml;' => 'ü',
            '&Auml;' => 'Ä',
            '&Ouml;' => 'Ö',
            '&Uuml;' => 'Ü',
            '&szlig;' => 'ß',
            '&nbsp;' => "\u{00A0}",
            '&#160;' => "\u{00A0}",
        ]);
    }

    private function encodeLegacyWindowsHighChars(string $htmlValue): string
    {
        $result = '';

        foreach (preg_split('//u', $htmlValue, -1, PREG_SPLIT_NO_EMPTY) as $char) {
            $ord = mb_ord($char, 'UTF-8');

            if ($ord === 0x00A0) {
                $result .= '&nbsp;';
                continue;
            }

            if ($ord >= 0x80 && !($ord >= 0x80 && $ord <= 0x9F)) {
                $result .= htmlentities($char, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                continue;
            }

            $result .= $char;
        }

        return $result;
    }
}
