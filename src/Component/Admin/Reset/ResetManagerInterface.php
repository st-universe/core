<?php

namespace Stu\Component\Admin\Reset;

use Ahc\Cli\IO\Interactor;

interface ResetManagerInterface
{
    public function performReset(Interactor $io): void;
}
