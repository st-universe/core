<?php

declare(strict_types=1);

namespace Stu\html;

use DirectoryIterator;
use Stu\StuTestCase;

class CorrectTwigFormatTest extends StuTestCase
{

    public function testNoBlankBetweenCurlyBraces(): void
    {
        $testedFiles = 0;

        foreach ($this->getTwigFilePaths() as $path) {

            $content = file_get_contents($path);
            $this->assertFalse(str_contains($content, '{ {'), $this->getErrorMessage($path));
            $this->assertFalse(str_contains($content, '} }'));

            $testedFiles++;
        }

        $this->assertTrue($testedFiles > 0);
    }

    private function getErrorMessage(string $path): string
    {
        return sprintf('Following twig file contains blank between curly braces: %s', $path);
    }

    /**
     * Returns an array of twig file paths.
     * 
     *  @return array<string> */
    private function getTwigFilePaths(): array
    {
        $result = [];

        $list = new DirectoryIterator(__DIR__ . '/../../src/html');

        foreach ($list as $file) {
            if (!$file->isDir() && str_ends_with($file->getFilename(), '.twig')) {
                $result[] = $file->getPath() . '/' . $file->getFilename();
            }
        }

        return $result;
    }
}
