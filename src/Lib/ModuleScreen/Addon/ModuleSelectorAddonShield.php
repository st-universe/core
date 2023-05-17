<?php

namespace Stu\Lib\ModuleScreen\Addon;

use Stu\Lib\ModuleScreen\GradientColor;
use Stu\Lib\ModuleScreen\ModuleSelectorAddonInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Repository\WeaponShieldRepositoryInterface;

//TODO unit tests
final class ModuleSelectorAddonShield implements ModuleSelectorAddonInterface
{
    private WeaponShieldRepositoryInterface $weaponShieldRepository;

    public function __construct(WeaponShieldRepositoryInterface $weaponShieldRepository)
    {
        $this->weaponShieldRepository = $weaponShieldRepository;
    }

    /**
     * @return array<int, array{factionId: int, gradientColor: string}>
     */
    public function getWeaponEffects(ModuleInterface $shieldModule): array
    {
        /** @var array<int, int[]> */
        $modificatorsPerFaction = [];

        foreach ($shieldModule->getWeaponShield() as $weaponShield) {
            $factionId = $weaponShield->getFactionId();

            if ($factionId === null) {
                continue;
            }

            if (!array_key_exists($factionId, $modificatorsPerFaction)) {
                $modificatorsPerFaction[$factionId] = [];
            }

            $modificatorsPerFaction[$factionId][] = $weaponShield->getModificator();
        }

        $result = [];

        [$lowest, $highest] = $this->weaponShieldRepository->getModificatorMinAndMax();

        foreach ($modificatorsPerFaction as $factionId => $modificators) {

            $sum = 0;

            foreach ($modificators as $modificator) {
                $sum += $modificator;
            }

            $gradientColor = GradientColor::calculateGradientColor((int)round($sum / count($modificators)), $lowest, $highest);

            $result[] = ['factionId' => $factionId, 'gradientColor' => $gradientColor];
        }

        return $result;
    }
}
