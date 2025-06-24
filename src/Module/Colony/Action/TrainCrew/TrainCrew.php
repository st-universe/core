<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\TrainCrew;

use Override;
use request;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Crew\CrewCountRetrieverInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CrewTrainingRepositoryInterface;

final class TrainCrew implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TRAIN_CREW';

    public function __construct(private ColonyFunctionManagerInterface $colonyFunctionManager, private ColonyLoaderInterface $colonyLoader, private CrewTrainingRepositoryInterface $crewTrainingRepository, private ColonyRepositoryInterface $colonyRepository, private ColonyLibFactoryInterface $colonyLibFactory, private CrewCountRetrieverInterface $crewCountRetriever) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $crewRemainingCount = $this->crewCountRetriever->getRemainingCount($user);

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId
        );
        $game->setView(ShowColony::VIEW_IDENTIFIER);
        $changeable = $colony->getChangeable();

        $localcrewlimit = $this->colonyLibFactory->createColonyPopulationCalculator(
            $colony
        )->getCrewLimit();

        $crewinlocalpool = $colony->getCrewAssignmentAmount();

        $trainableCrewPerTick = $this->crewCountRetriever->getTrainableCount($user) - $this->crewCountRetriever->getInTrainingCount($user);

        if ($localcrewlimit - $crewinlocalpool < $trainableCrewPerTick) {
            $trainableCrewPerTick = $localcrewlimit - $crewinlocalpool;
        }

        if ($trainableCrewPerTick > $crewRemainingCount) {
            $trainableCrewPerTick = $crewRemainingCount;
        }
        if ($trainableCrewPerTick < 0) {
            $trainableCrewPerTick = 0;
        }
        if ($trainableCrewPerTick > $changeable->getWorkless()) {
            $trainableCrewPerTick = $changeable->getWorkless();
        }

        $count = request::postStringFatal('crewcount');
        if ($count === 'm') {
            $count = $trainableCrewPerTick;
        } elseif ($count > $trainableCrewPerTick) {
            $count = $trainableCrewPerTick;
        } else {
            $count = (int)$count;
        }
        if ($count <= 0) {
            return;
        }
        if (!$this->colonyFunctionManager->hasActiveFunction($colony, BuildingFunctionEnum::ACADEMY)) {
            $game->addInformation(_('Es befindet sich keine aktivierte Akademie auf diesen Planeten'));
            return;
        }
        if ($trainableCrewPerTick <= 0) {
            $game->addInformation(_('Derzeit kann keine weitere Crew ausgebildet werden'));
            return;
        }

        $freeAssignmentCount = $this->colonyLibFactory->createColonyPopulationCalculator(
            $colony
        )->getFreeAssignmentCount();

        if ($freeAssignmentCount === 0) {
            $game->addInformation(_('Auf dieser Kolonie kann derzeit keine weitere Crew ausgebildet werden'));
            return;
        }
        if ($count > $freeAssignmentCount) {
            $count = $freeAssignmentCount;
        }

        for ($i = 0; $i < $count; $i++) {
            $crew = $this->crewTrainingRepository->prototype();

            $crew->setUser($game->getUser());
            $crew->setColony($colony);

            $this->crewTrainingRepository->save($crew);
        }
        $changeable->setWorkless($changeable->getWorkless() - $count);

        $this->colonyRepository->save($colony);

        $game->addInformationf(_('Es werden %d Crew auf dieser Kolonie ausgebildet'), $count);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
