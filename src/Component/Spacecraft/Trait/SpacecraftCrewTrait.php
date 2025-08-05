<?php

namespace Stu\Component\Spacecraft\Trait;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\GameControllerInterface;

trait SpacecraftCrewTrait
{
    use SpacecraftTrait;
    use SpacecraftSystemExistenceTrait;

    public function getNeededCrewCount(): int
    {
        $buildplan = $this->getThis()->getBuildplan();
        if ($buildplan === null) {
            return 0;
        }

        return $buildplan->getCrew();
    }

    public function getCrewCount(): int
    {
        return $this->getThis()->getCrewAssignments()->count();
    }

    public function getExcessCrewCount(): int
    {
        return $this->getCrewCount() - $this->getNeededCrewCount();
    }

    public function hasEnoughCrew(?GameControllerInterface $game = null): bool
    {
        $buildplan = $this->getThis()->getBuildplan();

        if ($buildplan === null) {
            if ($game !== null) {
                $game->getInfo()->addInformation(_("Keine Crew vorhanden"));
            }
            return false;
        }

        $result = $buildplan->getCrew() <= 0
            || $this->getCrewCount() >= $buildplan->getCrew();

        if (!$result && $game !== null) {
            $game->getInfo()->addInformationf(
                _("Es werden %d Crewmitglieder benötigt"),
                $buildplan->getCrew()
            );
        }

        return $result;
    }

    public function canMan(): bool
    {
        $buildplan = $this->getThis()->getBuildplan();

        return $buildplan !== null
            && $buildplan->getCrew() > 0
            && $this->hasSpacecraftSystem(SpacecraftSystemTypeEnum::LIFE_SUPPORT);
    }
}
