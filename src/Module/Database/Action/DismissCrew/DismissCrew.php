<?php

declare(strict_types=1);

namespace Stu\Module\Database\Action\DismissCrew;

use request;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Transfer\Wrapper\SpacecraftStorageCrewLogic;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Database\View\ShowCrewManagement\ShowCrewManagement;
use Stu\Module\Spacecraft\Lib\SpacecraftRemoverInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;

final class DismissCrew implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DISMISS_CREW';

    public function __construct(
        private CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private CrewRepositoryInterface $crewRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private SpacecraftStorageCrewLogic $spacecraftStorageCrewLogic,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private SpacecraftRemoverInterface $spacecraftRemover
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowCrewManagement::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $spacecrafts = [];
        $dismissedCrewCount = 0;

        foreach (array_unique(array_map('intval', request::postArray('crew_ids'))) as $crewId) {
            if ($crewId < 1) {
                continue;
            }

            $crewAssignment = $this->crewAssignmentRepository->find($crewId);
            if ($crewAssignment === null) {
                continue;
            }

            $crew = $crewAssignment->getCrew();
            if ($crew->getUserId() !== $user->getId()) {
                continue;
            }

            $spacecraft = $crewAssignment->getSpacecraft();
            if ($spacecraft !== null) {
                $spacecraftId = $spacecraft->getId();
                $spacecrafts[$spacecraftId] ??= [
                    'spacecraft' => $spacecraft,
                    'foreignCrewChange' => 0
                ];
                if ($spacecraft->getUser()->getId() !== $user->getId()) {
                    $spacecrafts[$spacecraftId]['foreignCrewChange']--;
                }
            }

            $crewAssignment->clearAssignment();
            $this->crewAssignmentRepository->delete($crewAssignment);
            $this->crewRepository->delete($crew);
            $dismissedCrewCount++;
        }

        foreach ($spacecrafts as $data) {
            $spacecraft = $data['spacecraft'];
            if ($spacecraft->getRump()->isEscapePods()) {
                if ($spacecraft->getCrewAssignments()->isEmpty()) {
                    $this->spacecraftRemover->remove($spacecraft);
                }
                continue;
            }

            $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);
            $this->spacecraftStorageCrewLogic->postCrewTransfer(
                $wrapper,
                $data['foreignCrewChange'],
                $game->getInfo()
            );

            if ($this->hasEnoughCrew($spacecraft)) {
                continue;
            }

            foreach ($this->spacecraftSystemManager->getActiveSystems($spacecraft) as $system) {
                if ($system->getSystemType() !== SpacecraftSystemTypeEnum::LIFE_SUPPORT) {
                    $this->spacecraftSystemManager->deactivate($wrapper, $system->getSystemType(), true);
                }
            }
        }

        if ($dismissedCrewCount > 0) {
            $game->getInfo()->addInformationf('%d Crewman wurde(n) entlassen', $dismissedCrewCount);
        }
    }

    private function hasEnoughCrew(Spacecraft $spacecraft): bool
    {
        if ($spacecraft->getRump()->getRoleId() !== SpacecraftRumpRoleEnum::SENSOR) {
            return $spacecraft->hasEnoughCrew();
        }

        $ownerCrewCount = 0;
        foreach ($spacecraft->getCrewAssignments() as $crewAssignment) {
            if ($crewAssignment->getCrew()->getUserId() === $spacecraft->getUser()->getId()) {
                $ownerCrewCount++;
            }
        }

        return $ownerCrewCount >= $spacecraft->getNeededCrewCount();
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
