<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Module;

interface ModuleRumpWrapperInterface
{
    /**
     * @return iterable<Module>
     */
    public function getModule(): iterable;

    public function getValue(?Module $module = null): int;

    public function getSecondValue(?Module $module = null): int;

    public function initialize(SpacecraftWrapperInterface $wrapper): ModuleRumpWrapperInterface;

    public function apply(SpacecraftWrapperInterface $wrapper): void;
}
