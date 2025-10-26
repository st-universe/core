<?php

namespace Stu\Lib\ModuleScreen\Addon;

use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Orm\Entity\Module;
use Stu\Orm\Repository\WeaponShieldRepositoryInterface;

final class ModuleSelectorAddonShield implements ModuleSelectorAddonInterface
{
    /** @var array<int>|null */
    private ?array $interval = null;

    public function __construct(private WeaponShieldRepositoryInterface $weaponShieldRepository, private GradientColorInterface $gradientColor)
    {
    }

    /**
     * return array<int, array{factionId: int, gradientColor: string, modificator: int}>
     */
    #[\Override]
    public function getModificators(Module $module): array
    {
        /** @var array<int, int[]> */
        $modificatorsPerFaction = [];

        foreach ($module->getWeaponShield() as $weaponShield) {
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

            $result[] = [
                'factionId' => $factionId,
                'gradientColor' => $gradientColor,
                'modificator' => $modificatorAverage
            ];
        }

        return $result;
    }
}
