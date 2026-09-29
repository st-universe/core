<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;

final class IndividualCrewTransfer
{
    private IndividualCrewTransferSideProvider $sideProvider;
    private IndividualCrewTransferSelectionValidator $selectionValidator;
    private IndividualCrewTransferCapacityValidator $capacityValidator;
    private IndividualCrewTransferExecutor $executor;

    public function __construct(
        ShipRumpCategoryRoleCrewRepositoryInterface $positionRepository,
        private UserCrewRankRepositoryInterface $rankRepository,
        CrewAssignmentRepositoryInterface $assignmentRepository,
        TroopTransferUtilityInterface $troopTransferUtility
    ) {
        $this->sideProvider = new IndividualCrewTransferSideProvider($positionRepository);
        $assignmentLookup = new IndividualCrewAssignmentLookup();
        $this->selectionValidator = new IndividualCrewTransferSelectionValidator($this->sideProvider, $assignmentLookup);
        $this->capacityValidator = new IndividualCrewTransferCapacityValidator($this->sideProvider);
        $this->executor = new IndividualCrewTransferExecutor($assignmentLookup, $assignmentRepository, $troopTransferUtility);
    }

    public function setTemplateVariables(
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        ViewControllerContext $game
    ): void {
        if (!$this->sideProvider->isSupported($source, $target)) {
            $game->setMacroInAjaxWindow('');
            $game->getInfo()->addInformation('Einzelne Crewman können hier nicht transferiert werden');
            return;
        }

        $user = $game->getUser();
        $game->setPageTitle('Einzelne Crewman transferieren');
        $game->setMacroInAjaxWindow('html/transfer/individualCrewTransfer.twig');
        $game->setTemplateVar('CREW_TRANSFER_SIDES', [
            $this->sideProvider->getSide($source, $user, false),
            $this->sideProvider->getSide($target, $user, true)
        ]);
        $rankNames = [];
        foreach (CrewSkillLevelEnum::cases() as $rank) {
            $rankNames[$rank->value] = $this->rankRepository->getRankName($user, $rank);
        }
        $game->setTemplateVar('CREW_RANK_NAMES', $rankNames);
    }

    public function transfer(
        string $placementsJson,
        StorageEntityWrapperInterface $source,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): void {
        if (!$this->sideProvider->isSupported($source, $target)) {
            $information->addInformation('Einzelne Crewman können hier nicht transferiert werden');
            return;
        }

        $selection = $this->selectionValidator->validate($placementsJson, $source, $target, $information);
        if ($selection === null) {
            return;
        }
        if (!$this->capacityValidator->validate($placementsJson, $source, $target, $selection, $information)) {
            return;
        }

        $this->executor->execute($placementsJson, $source, $target, $selection, $information);
    }
}
