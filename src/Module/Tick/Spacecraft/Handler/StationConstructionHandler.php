<?php

namespace Stu\Module\Tick\Spacecraft\Handler;

use Override;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Station\StationUtilityInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Station\Lib\StationWrapperInterface;
use Stu\Module\Tick\Spacecraft\SpacecraftTickFinishedException;

class StationConstructionHandler implements SpacecraftTickHandlerInterface
{
    public function __construct(
        private StationUtilityInterface $stationUtility
    ) {}

    #[Override]
    public function handleSpacecraftTick(
        SpacecraftWrapperInterface $wrapper,
        InformationInterface $information
    ): void {

        if (
            $wrapper instanceof StationWrapperInterface
            && $this->doConstructionStuff($wrapper, $information)
        ) {
            throw new SpacecraftTickFinishedException();
        }
    }

    private function doConstructionStuff(StationWrapperInterface $wrapper, InformationInterface $information): bool
    {
        $station = $wrapper->get();

        $progress =  $station->getConstructionProgress();
        if ($progress === null) {
            return false;
        }

        if ($progress->getRemainingTicks() === 0) {
            return false;
        }

        $isUnderConstruction = $station->getState() === SpacecraftStateEnum::UNDER_CONSTRUCTION;

        if (!$this->stationUtility->hasEnoughDockedWorkbees($station, $station->getRump())) {
            $neededWorkbees = $isUnderConstruction ? $station->getRump()->getNeededWorkbees() :
                (int)ceil($station->getRump()->getNeededWorkbees() / 2);

            $information->addInformationf(
                'Nicht genügend Workbees (%d/%d) angedockt um %s weiterführen zu können',
                $this->stationUtility->getDockedWorkbeeCount($station),
                $neededWorkbees ?? 0,
                $isUnderConstruction ? 'den Bau' : 'die Demontage'
            );
            return true;
        }

        if ($isUnderConstruction) {
            // raise hull
            $increase = (int)ceil($station->getMaxHull() / (2 * $station->getRump()->getBuildtime()));
            $station->getCondition()->changeHull($increase);
        }

        if ($progress->getRemainingTicks() === 1) {

            $information->addInformationf(
                '%s: %s bei %s fertiggestellt',
                $station->getRump()->getName(),
                $isUnderConstruction ? 'Bau' : 'Demontage',
                $station->getSectorString()
            );

            if ($isUnderConstruction) {
                $this->stationUtility->finishStation($progress);
            } else {
                $this->stationUtility->finishScrapping($progress, $information);
            }
        } else {
            $this->stationUtility->reduceRemainingTicks($progress);
        }

        return true;
    }
}
