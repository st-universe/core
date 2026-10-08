<?php

declare(strict_types=1);

namespace Stu\Extension;

use RuntimeException;

final class ExtensionManifestLoader
{
    /** @var array<string, mixed> */
    private static array $manifests = [];

    public static function load(string $file): mixed
    {
        $resolvedFile = realpath($file);
        if ($resolvedFile === false) {
            throw new RuntimeException('Cannot resolve extension manifest');
        }

        if (array_key_exists($resolvedFile, self::$manifests)) {
            return self::$manifests[$resolvedFile];
        }

        return self::$manifests[$resolvedFile] = require_once $resolvedFile;
    }
}
