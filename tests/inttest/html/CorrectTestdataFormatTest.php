<?php

declare(strict_types=1);

namespace Stu\Html;

use DirectoryIterator;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\StuTestCase;

class CorrectTestdataFormatTest extends StuTestCase
{
    /**
     * Returns an array of testdata migration file paths.
     *
     *  @return array<string> */
    public static function provideTestdataMigrationPaths(): array
    {
        $result = [];

        $list = new DirectoryIterator(__DIR__ . '/../../../testdata/Migrations');

        foreach ($list as $file) {
            if (!$file->isDir() && str_ends_with($file->getFilename(), '.php')) {
                $result[][] = $file->getPath() . '/' . $file->getFilename();
            }
        }

        return $result;
    }


    #[DataProvider('provideTestdataMigrationPaths')]
    public function testNoTrueOrFalseBooleans(string $path): void
    {
        $content = file_get_contents($path);
        $this->assertFalse(str_contains($content, ',true,'), $this->getErrorMessage($path));
        $this->assertFalse(str_contains($content, ', true,'), $this->getErrorMessage($path));
        $this->assertFalse(str_contains($content, ', true ,'), $this->getErrorMessage($path));
        $this->assertFalse(str_contains($content, ',false,'), $this->getErrorMessage($path));
        $this->assertFalse(str_contains($content, ', false,'), $this->getErrorMessage($path));
        $this->assertFalse(str_contains($content, ', false ,'), $this->getErrorMessage($path));
    }

    private function getErrorMessage(string $path): string
    {
        return sprintf("Following testdata migration file contains 'false' or 'true' (use 0 or 1 instead): %s", $path);
    }
}
