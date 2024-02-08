<?php

namespace Stu\Lib\ModuleScreen\Addon;

use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Lib\ModuleScreen\ModuleSelectorAddonInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Repository\WeaponShieldRepositoryInterface;

final class ModuleSelectorAddonShield implements ModuleSelectorAddonInterface
{
    private WeaponShieldRepositoryInterface $weaponShieldRepository;

    private GradientColorInterface $gradientColor;

    /** @var array<int>|null */
    private ?array $interval = null;

    public function __construct(
        WeaponShieldRepositoryInterface $weaponShieldRepository,
        GradientColorInterface $gradientColor
    ) {
        $this->weaponShieldRepository = $weaponShieldRepository;
        $this->gradientColor = $gradientColor;
    }

    /**
     * @return array<int, array{factionId: int, gradientColor: string, modificator: int}>
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

        if ($this->interval === null) {
            $this->interval = $this->weaponShieldRepository->getModificatorMinAndMax();
        }

        [$lowest, $highest] = $this->interval;

        foreach ($modificatorsPerFaction as $factionId => $modificators) {

            $sum = 0;

            foreach ($modificators as $modificator) {
                $sum += $modificator;
            }

            $modificatorAverage = (int)round($sum / count($modificators));

            $gradientColor = $this->gradientColor->calculateGradientColor($modificatorAverage, $lowest, $highest);

            $result[] = ['factionId' => $factionId, 'gradientColor' => $gradientColor, 'modificator' => $modificatorAverage];
        }

        return $result;
    }
}
