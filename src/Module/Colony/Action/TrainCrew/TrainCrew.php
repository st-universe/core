<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\TrainCrew;

use ColonyData;
use CrewTrainingData;
use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class TrainCrew implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_TRAIN_CREW';

    private $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $count = request::postStringFatal('crewcount');
        if ($count == INDICATOR_MAX) {
            $count = $this->getTrainableCrewCountPerTick($colony);
        } else {
            if ($count > $this->getTrainableCrewCountPerTick($colony)) {
                $count = $this->getTrainableCrewCountPerTick($colony);
            } else {
                $count = intval($count);
            }
        }
        if ($count <= 0) {
            return;
        }
        if (!$colony->hasActiveBuildingWithFunction(BUILDING_FUNCTION_ACADEMY)) {
            $game->addInformation(_('Es befindet sich keine aktivierte Akademie auf diesen Planeten'));
            return;
        }
        if ($this->getTrainableCrewCountPerTick($colony) <= 0) {
            $game->addInformation(_('Derzeit kann keine weitere Crew ausgebildet werden'));
            return;
        }
        $i = 0;
        while ($i < $count) {
            $i++;
            $crew = new CrewTrainingData;
            $crew->setUserId(currentUser()->getId());
            $crew->setColonyId($colony->getId());
            $crew->save();
        }
        $colony->lowerWorkless($count);
        $colony->save();
        $game->addInformation(sprintf(_('Es werden %d Crew ausgebildet'), $count));
    }

    private function getTrainableCrewCountPerTick(ColonyData $colony)
    {
        $count = currentUser()->getTrainableCrewCountMax() - currentUser()->getInTrainingCrewCount();
        if ($count > currentUser()->getCrewLeftCount()) {
            $count = currentUser()->getCrewLeftCount();
        }
        if ($count < 0) {
            $count = 0;
        }
        if ($count > $colony->getWorkless()) {
            return $colony->getWorkless();
        }
        return $count;
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
