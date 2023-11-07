<?php

declare(strict_types=1);

namespace Stu\html;

use DirectoryIterator;
use Stu\StuTestCase;

class CorrectXhtmlFormatTest extends StuTestCase
{

    /**
     * Returns an array of twig file paths.
     * 
     *  @return array<string> */
    public static function xhtmlFilePathDataProvider(): array
    {
        $result = [];

        $list = new DirectoryIterator(__DIR__ . '/../../src/html');

        foreach ($list as $file) {
            if (!$file->isDir() && str_ends_with($file->getFilename(), '.xhtml')) {
                $result[][] = $file->getPath() . '/' . $file->getFilename();
            }
        }

        return $result;
    }

    /**
     * @dataProvider xhtmlFilePathDataProvider
     */
    public function testNoBlankBetweenCurlyBraces(string $path): void
    {
        $content = file_get_contents($path);

        $this->assertFalse($this->containsBlankInCurlyBraces($content), $this->getErrorMessage($path));
    }

    private function containsBlankInCurlyBraces(string $inputString): bool
    {
        $pattern1 = '/\${\s[^}]+}/';
        $pattern2 = '/\${[^}]+\s}/';
        $pattern3 = '/\${[^}]+\s[^}]+}/';

        if (
            preg_match($pattern1, $inputString)
            || preg_match($pattern2, $inputString)
            || preg_match($pattern3, $inputString)
        ) {
            return true;
        } else {
            return false;
        }
    }

    private function getErrorMessage(string $path): string
    {
        return sprintf('Following xhtml file contains whitespaces in variable reference: %s', $path);
    }
}
