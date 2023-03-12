<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\ShipInterface;

interface ModuleRumpWrapperInterface
{
    /**
     * @return iterable<BuildplanModuleInterface>
     */
    public function getModule(): iterable;

    public function getValue(): int;

    public function apply(ShipInterface $ship): void;
}