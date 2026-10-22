<?php

declare(strict_types=1);

namespace Stu\Lib;

use Stu\StuTestCase;

final class StuBbCodeImageValidatorTest extends StuTestCase
{
    public function testValidationNeverOpensPlayerSelectedUrls(): void
    {
        stream_wrapper_unregister('http');
        stream_wrapper_register('http', ImageValidationStream::class);
        try {
            $validator = new StuBbCodeImageValidator();
            self::assertTrue($validator->validate('http://127.0.0.1/private'));
            self::assertTrue($validator->validate('https://example.test/image.png?a=1&b=2'));
            self::assertFalse($validator->validate('file:///tmp/image.png'));
            self::assertFalse($validator->validate('http://example.test/" onerror="window.test=1'));
            self::assertSame(0, ImageValidationStream::$opens);
        } finally {
            stream_wrapper_restore('http');
        }
    }
}

final class ImageValidationStream
{
    public mixed $context;
    public static int $opens = 0;

    public function stream_open(string $path, string $mode, int $options, ?string &$openedPath): bool
    {
        self::$opens++;
        return false;
    }
}
