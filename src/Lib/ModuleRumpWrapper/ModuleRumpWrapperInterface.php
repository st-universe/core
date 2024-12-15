<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ModuleInterface;

interface ModuleRumpWrapperInterface
{
    /**
     * @return iterable<ModuleInterface>
     */
    public function getModule(): iterable;

    public function getValue(?ModuleInterface $module = null): int;

    public function getSecondValue(?ModuleInterface $module = null): ?int;

    public function apply(SpacecraftWrapperInterface $wrapper): void;
}
