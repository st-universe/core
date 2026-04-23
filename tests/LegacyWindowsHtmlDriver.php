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

        // Keep the historic snapshot encoding from older Windows runs so
        // cross-platform snapshot updates do not rewrite thousands of lines.
        $htmlValue = mb_convert_encoding($htmlValue, 'UTF-8', 'ISO-8859-1');
        $htmlValue = preg_replace_callback(
            '/[^\x00-\x7F]/u',
            static fn(array $matches): string => htmlentities(
                $matches[0],
                ENT_QUOTES | ENT_SUBSTITUTE,
                'UTF-8'
            ),
            $htmlValue
        ) ?? $htmlValue;

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
}
