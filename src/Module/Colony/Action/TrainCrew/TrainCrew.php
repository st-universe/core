<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\TrainCrew;

use request;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;

final class TrainCrew implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TRAIN_CREW';

    private ColonyLoaderInterface $colonyLoader;

    private CrewTrainingRepositoryInterface $crewTrainingRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private CrewCountRetrieverInterface $crewCountRetriever;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        CrewTrainingRepositoryInterface $crewTrainingRepository,
        ColonyRepositoryInterface $colonyRepository,
        CrewCountRetrieverInterface $crewCountRetriever
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->crewTrainingRepository = $crewTrainingRepository;
        $this->colonyRepository = $colonyRepository;
        $this->crewCountRetriever = $crewCountRetriever;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $crewRemainingCount = $this->crewCountRetriever->getRemainingCount($user);

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );
        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $trainableCrewPerTick = $this->crewCountRetriever->getTrainableCount($user) - $this->crewCountRetriever->getInTrainingCount($user);
        if ($trainableCrewPerTick > $crewRemainingCount) {
            $trainableCrewPerTick = $crewRemainingCount;
        }
        if ($trainableCrewPerTick < 0) {
            $trainableCrewPerTick = 0;
        }
        if ($trainableCrewPerTick > $colony->getWorkless()) {
            $trainableCrewPerTick = $colony->getWorkless();
        }

        $count = request::postStringFatal('crewcount');
        if ($count == 'm') {
            $count = $trainableCrewPerTick;
        } else {
            if ($count > $trainableCrewPerTick) {
                $count = $trainableCrewPerTick;
            } else {
                $count = (int)$count;
            }
        }
        if ($count <= 0) {
            return;
        }
        if (!$colony->hasActiveBuildingWithFunction(BuildingEnum::BUILDING_FUNCTION_ACADEMY)) {
            $game->addInformation(_('Es befindet sich keine aktivierte Akademie auf diesen Planeten'));
            return;
        }
        if ($trainableCrewPerTick <= 0) {
            $game->addInformation(_('Derzeit kann keine weitere Crew ausgebildet werden'));
            return;
        }
        if ($colony->getFreeAssignmentCount() === 0) {
            $game->addInformation(_('Auf dieser Kolonie kann derzeit keine weitere Crew ausgebildet werden'));
            return;
        }
        if ($count > $colony->getFreeAssignmentCount()) {
            $count = $colony->getFreeAssignmentCount();
        }

        for ($i = 0; $i < $count; $i++) {
            $crew = $this->crewTrainingRepository->prototype();

            $crew->setUser($game->getUser());
            $crew->setColony($colony);

            $this->crewTrainingRepository->save($crew);
        }
        $colony->setWorkless($colony->getWorkless() - $count);

        $this->colonyRepository->save($colony);

        $game->addInformationf(_('Es werden %d Crew auf dieser Kolonie ausgebildet'), $count);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
