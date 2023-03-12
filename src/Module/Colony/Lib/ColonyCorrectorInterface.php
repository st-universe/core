<?php

namespace Stu\Module\Colony\Lib;

interface ColonyCorrectorInterface
{
    public function correct(bool $doDump = true): void;
}
