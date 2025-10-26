<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use BadMethodCallException;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftRumpBaseValues;
use Stu\Orm\Entity\SpacecraftRump;

abstract class ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{
    /** @var null|array<int, Module> */
    private ?array $modules = null;

    protected SpacecraftRumpBaseValues $rumpBaseValues;

    public function __construct(
        protected SpacecraftRump $rump,
        private ?SpacecraftBuildplan $buildplan
    ) {
        $this->rumpBaseValues = $rump->getBaseValues();
    }

    abstract public function getModuleType(): SpacecraftModuleTypeEnum;

    #[\Override]
    public function getModule(): iterable
    {
        if ($this->modules === null) {
            $buildplan = $this->buildplan;
            $this->modules = $buildplan === null
                ? []
                : $buildplan->getModulesByType($this->getModuleType())->toArray();
        }

        return $this->modules;
    }

    #[\Override]
    public function getSecondValue(?Module $module = null): int
    {
        throw new BadMethodCallException(sprintf('not implemented for moduleType: %s', $this->getModuleType()->name));
    }

    #[\Override]
    public function initialize(SpacecraftWrapperInterface $wrapper): ModuleRumpWrapperInterface
    {
        //override if neccessary
        return $this;
    }
}
