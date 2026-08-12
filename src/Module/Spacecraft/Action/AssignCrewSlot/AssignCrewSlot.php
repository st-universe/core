<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\AssignCrewSlot;

use request;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowCrewAssignmentManagement\ShowCrewAssignmentManagement;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;

final class AssignCrewSlot implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ASSIGN_CREW_SLOT';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private ShipRumpCategoryRoleCrewRepositoryInterface $shipRumpCategoryRoleCrewRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowCrewAssignmentManagement::VIEW_IDENTIFIER);

        $user = $game->getUser();
        $spacecraft = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $user->getId(),
            true
        );
        $crewAssignment = $this->crewAssignmentRepository->find(request::postIntFatal('crewid'));
        $slot = CrewTypeEnum::tryFrom(request::postIntFatal('slot'));
        $swapCrewId = request::postInt('swapcrewid');

        if (
            $crewAssignment === null
            || $slot === null
            || $crewAssignment->getCrew()->getUserId() !== $user->getId()
            || $crewAssignment->getSpacecraft()?->getId() !== $spacecraft->getId()
            || $crewAssignment->getSlot() === null
        ) {
            throw new AccessViolationException();
        }

        if ($swapCrewId > 0) {
            $swapCrewAssignment = $this->crewAssignmentRepository->find($swapCrewId);
            if (
                $swapCrewAssignment === null
                || $swapCrewAssignment === $crewAssignment
                || $swapCrewAssignment->getCrew()->getUserId() !== $user->getId()
                || $swapCrewAssignment->getSpacecraft()?->getId() !== $spacecraft->getId()
                || $swapCrewAssignment->getSlot() === null
            ) {
                throw new AccessViolationException();
            }

            $sourceSlot = $crewAssignment->getSlot();
            $crewAssignment->setSlot($swapCrewAssignment->getSlot());
            $swapCrewAssignment->setSlot($sourceSlot);
            $this->crewAssignmentRepository->save($crewAssignment);
            $this->crewAssignmentRepository->save($swapCrewAssignment);

            return;
        }

        if ($slot !== CrewTypeEnum::CREWMAN) {
            $rump = $spacecraft->getRump();
            $rumpRole = $rump->getShipRumpRole();
            $config = $rumpRole === null
                ? null
                : $this->shipRumpCategoryRoleCrewRepository->getByShipRumpCategoryAndRole(
                    $rump->getShipRumpCategory()->getId(),
                    $rumpRole->getId()
                );
            if ($config === null) {
                return;
            }

            $assignedCrewCount = 0;
            foreach ($spacecraft->getCrewAssignments() as $otherCrewAssignment) {
                if (
                    $otherCrewAssignment->getCrew()->getId() !== $crewAssignment->getCrew()->getId()
                    && $otherCrewAssignment->getCrew()->getUserId() === $user->getId()
                    && $otherCrewAssignment->getSlot() === $slot
                ) {
                    $assignedCrewCount++;
                }
            }

            if ($assignedCrewCount >= $config->getCrewForPosition($slot)) {
                return;
            }
        }

        $crewAssignment->setSlot($slot);
        $this->crewAssignmentRepository->save($crewAssignment);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
