<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipRumpInterface;

abstract class ModuleRumpWrapperBase implements ModuleRumpWrapperInterface
{
    protected ShipRumpInterface $rump;
    private ?ShipBuildplanInterface $buildplan;

    /** @var null|array<int, ModuleInterface> */
    private ?array $modules = null;

    public function __construct(ShipRumpInterface $rump, ?ShipBuildplanInterface $buildplan)
    {
        $this->rump = $rump;
        $this->buildplan = $buildplan;
    }

    public abstract function getModuleType(): ShipModuleTypeEnum;

    public function getModule(): iterable
    {
        if ($this->modules === null) {
            $buildplan = $this->buildplan;
            if ($buildplan === null) {
                $this->modules = [];
            } else {
                $this->modules = array_map(
                    fn (BuildplanModuleInterface $buildplanModule): ModuleInterface
                    => $buildplanModule->getModule(),
                    $buildplan->getModulesByType($this->getModuleType())
                );
            }
        }

        return $this->modules;
    }
}
