<?php

namespace Stu\Lib\ModuleScreen\Addon;

use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Lib\ModuleScreen\ModuleSelectorAddonInterface;
use Stu\Orm\Repository\TorpedoHullRepositoryInterface;
use Stu\Orm\Repository\WeaponShieldRepositoryInterface;

final class ModuleSelectorAddonFactory implements ModuleSelectorAddonFactoryInterface
{
    private TorpedoHullRepositoryInterface $torpedoHullRepository;

    private WeaponShieldRepositoryInterface $weaponShieldRepository;

    private GradientColorInterface $gradientColor;

    public function __construct(
        TorpedoHullRepositoryInterface $torpedoHullRepository,
        WeaponShieldRepositoryInterface $weaponShieldRepository,
        GradientColorInterface $gradientColor
    ) {
        $this->torpedoHullRepository = $torpedoHullRepository;
        $this->weaponShieldRepository = $weaponShieldRepository;
        $this->gradientColor = $gradientColor;
    }

    public function createModuleSelectorAddon(int $moduleType): ?ModuleSelectorAddonInterface
    {
        switch ($moduleType) {
            case ShipModuleTypeEnum::MODULE_TYPE_HULL:
                return new ModuleSelectorAddonHull(
                    $this->torpedoHullRepository,
                    $this->gradientColor
                );
            case ShipModuleTypeEnum::MODULE_TYPE_SHIELDS:
                return new ModuleSelectorAddonShield(
                    $this->weaponShieldRepository,
                    $this->gradientColor
                );
            default:
                return null;
        }
    }
}
