<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowCrewAssignmentManagement;

use request;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;

final class ShowCrewAssignmentManagement implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_CREW_ASSIGNMENT_MANAGEMENT';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private ShipRumpCategoryRoleCrewRepositoryInterface $shipRumpCategoryRoleCrewRepository,
        private UserCrewRankRepositoryInterface $userCrewRankRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $spacecraft = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $user->getId(),
            true
        );
        $rump = $spacecraft->getRump();
        $rumpRole = $rump->getShipRumpRole();
        $config = $rumpRole === null
            ? null
            : $this->shipRumpCategoryRoleCrewRepository->getByShipRumpCategoryAndRole(
                $rump->getShipRumpCategory()->getId(),
                $rumpRole->getId()
            );

        $crewBySlot = [];
        foreach ($spacecraft->getCrewAssignments() as $crewAssignment) {
            if ($crewAssignment->getCrew()->getUserId() !== $user->getId()) {
                continue;
            }

            $slot = $crewAssignment->getSlot();
            if ($slot === null) {
                continue;
            }

            $crewBySlot[$slot->value][] = $crewAssignment;
        }

        $positions = [];
        foreach (CrewTypeEnum::getOrder() as $position) {
            $positions[] = [
                'position' => $position,
                'capacity' => $position === CrewTypeEnum::CREWMAN
                    ? null
                    : $config?->getCrewForPosition($position) ?? 0,
                'crewAssignments' => $crewBySlot[$position->value] ?? []
            ];
        }

        $game->setPageTitle('Crewposten verwalten');
        $game->setMacroInAjaxWindow('html/spacecraft/crewAssignmentManagement.twig');
        $game->setTemplateVar('SPACECRAFT', $spacecraft);
        $game->setTemplateVar('POSITIONS', $positions);
        $crewRankNames = [];
        foreach (CrewSkillLevelEnum::cases() as $rank) {
            $crewRankNames[$rank->value] = $this->userCrewRankRepository->getRankName($user, $rank);
        }
        $game->setTemplateVar('CREW_RANK_NAMES', $crewRankNames);
    }
}
