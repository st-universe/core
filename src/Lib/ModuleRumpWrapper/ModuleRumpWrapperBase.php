<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;

abstract class ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{
    /** @var null|array<int, ModuleInterface> */
    private ?array $modules = null;

    public function __construct(protected SpacecraftRumpInterface $rump, private ?SpacecraftBuildplanInterface $buildplan) {}

    abstract public function getModuleType(): SpacecraftModuleTypeEnum;

    #[Override]
    public function getModule(): iterable
    {
        if ($this->modules === null) {
            $buildplan = $this->buildplan;
            if ($buildplan === null) {
                $this->modules = [];
            } else {
                $this->modules = $buildplan
                    ->getModulesByType($this->getModuleType())
                    ->toArray();
            }
        }

        return $this->modules;
    }

    #[Override]
    public function getSecondValue(?ModuleInterface $module = null): int
    {
        throw new RuntimeException(sprintf('not implemented for moduleType: %s', $this->getModuleType()->name));
    }

    #[Override]
    public function initialize(SpacecraftWrapperInterface $wrapper): ModuleRumpWrapperInterface
    {
        //override if neccessary
        return $this;
    }
}
