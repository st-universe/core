<?php

declare(strict_types=1);

namespace Stu;

use Stu\Config\Init;
use Stu\Lib\Component\ComponentEnumInterface;
use Stu\Lib\Component\ComponentLoaderInterface;

class StuMocks
{
    public static function registerStubbedComponent(ComponentEnumInterface $componentEnum): void
    {
        Init::getContainer()
            ->get(ComponentLoaderInterface::class)
            ->registerStubbedComponent($componentEnum);
    }
}
