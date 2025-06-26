<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;

final class UplinkShipSystem extends AbstractSpacecraftSystemType implements SpacecraftSystemTypeInterface
{
    public const int MAX_FOREIGNERS = 3;

    #[Override]
    public function getSystemType(): SpacecraftSystemTypeEnum
    {
        return SpacecraftSystemTypeEnum::UPLINK;
    }

    #[Override]
    public function checkActivationConditions(SpacecraftWrapperInterface $wrapper, string &$reason): bool
    {
        $spacecraft = $wrapper->get();

        $minOwnCrew = 0;
        $buildplan = $spacecraft->getBuildplan();
        if ($buildplan !== null) {
            $minOwnCrew = $buildplan->getCrew();
        }
        if (!$this->hasForeignCrew($spacecraft)) {
            $reason = _('keine fremde Crew an Bord ist');
            return false;
        }
        if ($this->getOwnCrewCount($spacecraft) < $minOwnCrew) {
            $reason = sprintf(_('mindestens %d eigene Crewmitglieder benötigt werden'), $minOwnCrew);
            return false;
        }

        return true;
    }

    private function getOwnCrewCount(Spacecraft $spacecraft): int
    {
        $count = 0;
        foreach ($spacecraft->getCrewAssignments() as $spacecraftCrew) {
            if ($spacecraftCrew->getCrew()->getUser() === $spacecraft->getUser()) {
                $count++;
            }
        }
        return $count;
    }

    private function hasForeignCrew(Spacecraft $spacecraft): bool
    {
        foreach ($spacecraft->getCrewAssignments() as $spacecraftCrew) {
            if ($spacecraftCrew->getCrew()->getUser() !== $spacecraft->getUser()) {
                return true;
            }
        }

        return false;
    }

    #[Override]
    public function getEnergyUsageForActivation(): int
    {
        return 0;
    }

    #[Override]
    public function getEnergyConsumption(): int
    {
        return 5;
    }
}
