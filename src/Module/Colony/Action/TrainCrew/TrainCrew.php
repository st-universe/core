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
        $user = $game->getUser();
        $userId = $user->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $trainableCrewPerTick = $user->getTrainableCrewCountMax() - $user->getInTrainingCrewCount();
        if ($trainableCrewPerTick > $user->getCrewLeftCount()) {
            $trainableCrewPerTick = $user->getCrewLeftCount();
        }
        if ($trainableCrewPerTick < 0) {
            $trainableCrewPerTick = 0;
        }
        if ($trainableCrewPerTick > $colony->getWorkless()) {
            $trainableCrewPerTick = $colony->getWorkless();
        }

        $count = request::postStringFatal('crewcount');
        if ($count == INDICATOR_MAX) {
            $count = $trainableCrewPerTick;
        } else {
            if ($count > $trainableCrewPerTick) {
                $count = $trainableCrewPerTick;
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
        if ($trainableCrewPerTick <= 0) {
            $game->addInformation(_('Derzeit kann keine weitere Crew ausgebildet werden'));
            return;
        }
        $i = 0;
        while ($i < $count) {
            $i++;
            $crew = new CrewTrainingData();
            $crew->setUserId($userId);
            $crew->setColonyId($colony->getId());
            $crew->save();
        }
        $colony->lowerWorkless($count);
        $colony->save();
        $game->addInformationf(_('Es werden %d Crew ausgebildet'), $count);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
