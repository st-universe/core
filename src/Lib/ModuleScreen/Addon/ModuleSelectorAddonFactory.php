<?php

namespace Stu\Lib\ModuleScreen\Addon;

use Override;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Orm\Repository\TorpedoHullRepositoryInterface;
use Stu\Orm\Repository\WeaponShieldRepositoryInterface;

final class ModuleSelectorAddonFactory implements ModuleSelectorAddonFactoryInterface
{
    public function __construct(
        private TorpedoHullRepositoryInterface $torpedoHullRepository,
        private WeaponShieldRepositoryInterface $weaponShieldRepository,
        private GradientColorInterface $gradientColor
    ) {
    }

    #[Override]
    public function createModuleSelectorAddon(ShipModuleTypeEnum $moduleType): ?ModuleSelectorAddonInterface
    {
        switch ($moduleType) {
            case ShipModuleTypeEnum::HULL:
                return new ModuleSelectorAddonHull(
                    $this->torpedoHullRepository,
                    $this->gradientColor
                );
            case ShipModuleTypeEnum::SHIELDS:
                return new ModuleSelectorAddonShield(
                    $this->weaponShieldRepository,
                    $this->gradientColor
                );
            default:
                return null;
        }
    }
}
