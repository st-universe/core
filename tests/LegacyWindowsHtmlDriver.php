<?php

declare(strict_types=1);

namespace Stu;

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

        return $this->normalizeLineEndings($data);
    }

    public function extension(): string
    {
        return 'html';
    }

    public function match($expected, $actual)
    {
        Assert::assertEquals($expected, $this->serialize($actual));
    }

    private function normalizeLineEndings(string $htmlValue): string
    {
        $htmlValue = str_replace(["\r\n", "\r"], "\n", $htmlValue);

        return rtrim($htmlValue, "\n") . "\n";
    }
}
