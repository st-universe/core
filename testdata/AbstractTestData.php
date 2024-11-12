<?php

declare(strict_types=1);

namespace Stu;

use Psr\Container\ContainerInterface;
use Stu\Config\Init;

abstract class AbstractTestData implements TestDataInterface
{
    protected ContainerInterface $dic;

    public function __construct()
    {
        $this->dic = Init::getContainer();
    }
}
