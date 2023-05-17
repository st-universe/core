<?php

namespace Stu\Lib\ModuleScreen\Addon;

use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Lib\ModuleScreen\ModuleSelectorAddonInterface;
use Stu\Orm\Repository\TorpedoHullRepositoryInterface;
use Stu\Orm\Repository\WeaponShieldRepositoryInterface;

//TODO unit tests
final class ModuleSelectorAddonFactory implements ModuleSelectorAddonFactoryInterface
{
    private TorpedoHullRepositoryInterface $torpedoHullRepository;

    private WeaponShieldRepositoryInterface $weaponShieldRepository;

    public function __construct(
        TorpedoHullRepositoryInterface $torpedoHullRepository,
        WeaponShieldRepositoryInterface $weaponShieldRepository
    ) {
        $this->torpedoHullRepository = $torpedoHullRepository;
        $this->weaponShieldRepository = $weaponShieldRepository;
    }

    public function createModuleSelectorAddon(int $moduleType): ?ModuleSelectorAddonInterface
    {
        switch ($moduleType) {
            case ShipModuleTypeEnum::MODULE_TYPE_HULL:
                return new ModuleSelectorAddonHull($this->torpedoHullRepository);
            case ShipModuleTypeEnum::MODULE_TYPE_SHIELDS:
                return new ModuleSelectorAddonShield($this->weaponShieldRepository);
            default:
                return null;
        }
    }
}
