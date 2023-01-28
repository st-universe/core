<?php

declare(strict_types=1);

namespace Stu;
use Ahc\Cli\IO\Interactor;
use Ahc\Cli\Output\Writer;

class CliInteractorHelper extends Interactor
{
    public function eol($n = 1): Writer
    {
        return parent::eol($n);
    }

    public function error(
        string $text,
        bool $eol = false
    ): Writer {
        return parent::error(
            $text,
            $eol
        );
    }

    public function ok(
        string $text,
        bool $eol = false
    ): Writer {
        return parent::error(
            $text,
            $eol
        );
    }

    public function info(
        string $text,
        bool $eol = false
    ): Writer {
        return parent::info(
            $text,
            $eol
        );
    }

    public function table(array $rows, array $styles = []): Writer
    {
        return parent::__call('table', [$rows, $styles]);
    }
}