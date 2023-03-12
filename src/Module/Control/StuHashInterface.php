<?php

namespace Stu\Module\Control;

interface StuHashInterface
{
    public function hash(string $data): string;
}
