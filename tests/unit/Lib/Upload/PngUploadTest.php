<?php

declare(strict_types=1);

namespace Stu\Lib\Upload;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PngUploadTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/stu-png-' . bin2hex(random_bytes(8));
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory . '/*') ?: [] as $path) {
            unlink($path);
        }
        rmdir($this->directory);
    }

    public function testOnlyUploadedFilesAreAcceptedAtTheUploadBoundary(): void
    {
        $image = imagecreatetruecolor(2, 2);
        self::assertNotFalse($image);
        $path = $this->directory . '/input.png';
        imagepng($image, $path);
        self::assertNull(new PngUpload()->readUploaded($path, UPLOAD_ERR_OK, 1000000, 2048, 2048, 4000000));
        self::assertNull(new PngUpload()->readUploaded($path, UPLOAD_ERR_PARTIAL, 1000000, 2048, 2048, 4000000));
    }

    public function testPngIsFreshlyEncodedWithoutTrailingPayload(): void
    {
        $image = imagecreatetruecolor(2, 2);
        self::assertNotFalse($image);
        $input = $this->directory . '/input.png';
        imagepng($image, $input);
        file_put_contents($input, '<?php echo "untrusted"; ?>', FILE_APPEND);
        $upload = new PngUpload();
        $decoded = $upload->readFile($input, 1000000, 2048, 2048, 4000000);
        self::assertNotNull($decoded);
        $output = $this->directory . '/output.png';
        self::assertTrue($upload->write($decoded, $output, 1000000));
        self::assertStringNotContainsString('untrusted', (string) file_get_contents($output));
        self::assertSame(IMAGETYPE_PNG, getimagesize($output)[2]);
        self::assertSame(2, imagesx($decoded));
        self::assertSame(2, imagesy($decoded));
    }

    #[DataProvider('invalidImages')]
    public function testInvalidOrOversizedInputIsRejected(string $content, int $maximumBytes): void
    {
        $path = $this->directory . '/input.png';
        file_put_contents($path, $content);
        self::assertNull(new PngUpload()->readFile($path, $maximumBytes, 2048, 2048, 4000000));
    }

    public static function invalidImages(): iterable
    {
        yield 'arbitrary content' => ['<?php echo "not an image";', 1000000];
        yield 'empty' => ['', 1000000];
        yield 'compressed bytes' => [str_repeat('x', 101), 100];
        yield 'truncated PNG' => ["\x89PNG\r\n\x1a\n", 1000000];
        yield 'huge dimensions' => [self::header(100000, 100000), 1000000];
        yield 'pixel ceiling' => [self::header(2048, 2048), 1000000];
    }

    private static function header(int $width, int $height): string
    {
        $data = pack('NNC5', $width, $height, 8, 6, 0, 0, 0);
        return "\x89PNG\r\n\x1a\n" . pack('N', strlen($data)) . 'IHDR' . $data . pack('N', crc32('IHDR' . $data)) . pack('N', 0) . 'IEND' . pack('N', crc32('IEND'));
    }
}
