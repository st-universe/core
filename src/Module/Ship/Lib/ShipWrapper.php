<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Spacecraft\System\Data\AstroLaboratorySystemData;
use Stu\Component\Spacecraft\System\Data\BussardCollectorSystemData;
use Stu\Component\Spacecraft\System\Data\TrackerSystemData;
use Stu\Component\Spacecraft\System\Data\WebEmitterSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapper;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Orm\Entity\Ship;

//TODO increase coverage
/**
 * @extends SpacecraftWrapper<Ship>
 */
final class ShipWrapper extends SpacecraftWrapper implements ShipWrapperInterface
{
    #[\Override]
    public function get(): Ship
    {
        return $this->spacecraft;
    }

    #[\Override]
    public function getFleetWrapper(): ?FleetWrapperInterface
    {
        $fleet = $this->spacecraft->getFleet();
        if ($fleet === null) {
            return null;
        }

        return $this->spacecraftWrapperFactory->wrapFleet($fleet);
    }

    #[\Override]
    public function canLandOnCurrentColony(): bool
    {
        if ($this->spacecraft->getRump()->getCommodity() === null) {
            return false;
        }
        if ($this->spacecraft->isShuttle()) {
            return false;
        }

        $currentColony = $this->spacecraft->getStarsystemMap() !== null ? $this->spacecraft->getStarsystemMap()->getColony() : null;

        if ($currentColony === null) {
            return false;
        }
        if ($currentColony->getUser()->getId() !== $this->spacecraft->getUser()->getId()) {
            return false;
        }

        return $this->colonyLibFactory
            ->createColonySurface($currentColony)
            ->hasAirfield();
    }

    #[\Override]
    public function canBeRetrofitted(): bool
    {
        if (!$this->isUnalerted()) {
            return false;
        }

        if ($this->spacecraft->getFleet() !== null) {
            return false;
        }

        if ($this->spacecraft->isShielded()) {
            return false;
        }

        if ($this->spacecraft->isCloaked()) {
            return false;
        }

        if ($this->spacecraft->getUser() != $this->game->getUser()) {
            return false;
        }

        if (
            $this->spacecraft->getBuildplan() != null
            && $this->spacecraft->getBuildplan()->getUser() != $this->game->getUser()
        ) {
            return false;
        }

        return true;
    }

    #[\Override]
    public function getTractoringSpacecraftWrapper(): ?SpacecraftWrapperInterface
    {
        $tractoringSpacecraft = $this->spacecraft->getTractoringSpacecraft();
        if ($tractoringSpacecraft === null) {
            return null;
        }

        return $this->spacecraftWrapperFactory->wrapSpacecraft($tractoringSpacecraft);
    }

    #[\Override]
    public function getDockedToStationWrapper(): ?StationWrapperInterface
    {
        $dockedTo = $this->spacecraft->getDockedTo();
        if ($dockedTo === null) {
            return null;
        }

        return $this->spacecraftWrapperFactory->wrapStation($dockedTo);
    }

    #[\Override]
    public function getTrackerSystemData(): ?TrackerSystemData
    {
        return $this->getSpecificShipSystem(
            SpacecraftSystemTypeEnum::TRACKER,
            TrackerSystemData::class
        );
    }

    #[\Override]
    public function getBussardCollectorSystemData(): ?BussardCollectorSystemData
    {
        return $this->getSpecificShipSystem(
            SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR,
            BussardCollectorSystemData::class
        );
    }

    #[\Override]
    public function getWebEmitterSystemData(): ?WebEmitterSystemData
    {
        return $this->getSpecificShipSystem(
            SpacecraftSystemTypeEnum::THOLIAN_WEB,
            WebEmitterSystemData::class
        );
    }

    #[\Override]
    public function getAstroLaboratorySystemData(): ?AstroLaboratorySystemData
    {
        return $this->getSpecificShipSystem(
            SpacecraftSystemTypeEnum::ASTRO_LABORATORY,
            AstroLaboratorySystemData::class
        );
    }
}
