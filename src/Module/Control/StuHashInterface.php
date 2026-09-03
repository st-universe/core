<?php

declare(strict_types=1);

namespace Stu\Module\Control;

interface StuHashInterface
{
    public function hash(string $data): string;
}
