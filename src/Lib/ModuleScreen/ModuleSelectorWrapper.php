<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use request;
use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\ShipRumpInterface;
use Stu\Orm\Entity\UserInterface;

final class ModuleSelectorWrapper implements ModuleSelectorWrapperInterface
{
    private ModuleInterface $module;

    private ShipRumpInterface $rump;

    private UserInterface $user;

    private ?ShipBuildplanInterface $buildplan;

    public function __construct(
        ModuleInterface $module,
        ShipRumpInterface $rump,
        UserInterface $user,
        ?ShipBuildplanInterface $buildplan = null
    ) {
        $this->module = $module;
        $this->rump = $rump;
        $this->user = $user;
        $this->buildplan = $buildplan;
    }

    public function isChosen(): bool
    {
        if ($this->buildplan !== null) {
            $module_id_list = array_map(
                fn (BuildplanModuleInterface $buildplanModule): int => $buildplanModule->getModuleId(),
                $this->buildplan->getModulesByType($this->module->getType())
            );
            if (in_array($this->module->getId(), $module_id_list)) {
                return true;
            }
        }
        $request = request::postArray('mod_' . $this->module->getType());
        return array_key_exists($this->module->getId(), $request);
    }

    public function getBuildplan(): ?ShipBuildplanInterface
    {
        return $this->buildplan;
    }

    public function getModule(): ModuleInterface
    {
        return $this->module;
    }

    public function getNeededCrew(): int
    {
        return $this->getModule()->getCrewByFactionAndRumpLvl($this->user->getFactionId(), $this->rump->getModuleLevel());
    }
}
