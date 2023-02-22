<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use request;
use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;

final class ModuleSelectorWrapper implements ModuleSelectorWrapperInterface
{
    private $module;

    private $buildplan;

    public function __construct(ModuleInterface $module, ?ShipBuildplanInterface $buildplan = null)
    {
        $this->module = $module;
        $this->buildplan = $buildplan;
    }

    public function isChosen(): bool
    {
        if ($this->buildplan !== null) {
            $module_id_list = array_map(
                function (BuildplanModuleInterface $buildplanModule): int {
                    return $buildplanModule->getModuleId();
                },
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
}
