<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ShowCrewManagement;

use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;

final class ShowCrewManagement implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_CREW_MANAGEMENT';

    public function __construct(
        private CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private UserCrewRankRepositoryInterface $userCrewRankRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $crewAssignments = $this->crewAssignmentRepository->findBy(['user' => $user]);

        usort(
            $crewAssignments,
            static fn (CrewAssignment $left, CrewAssignment $right): int =>
                $right->getCrew()->getHighestSkillExpertise() <=> $left->getCrew()->getHighestSkillExpertise()
                ?: strcasecmp($left->getCrew()->getName(), $right->getCrew()->getName())
        );

        $crewRankNames = [];
        $crewRankExpertises = [];
        foreach (CrewSkillLevelEnum::cases() as $rank) {
            $crewRankNames[$rank->value] = $this->userCrewRankRepository->getRankName($user, $rank);
            $crewRankExpertises[$rank->value] = $rank->getNeededExpertise();
        }

        $game->appendNavigationPart('database.php', _('Datenbank'));
        $game->appendNavigationPart(
            sprintf('database.php?%s=1', self::VIEW_IDENTIFIER),
            _('Crew')
        );
        $game->setPageTitle(_('/ Datenbank / Crew'));
        $game->setViewTemplate('html/database/crewManagement.twig');
        $game->setTemplateVar('CREW_ASSIGNMENTS', $crewAssignments);
        $game->setTemplateVar('CREW_RANKS', CrewSkillLevelEnum::cases());
        $game->setTemplateVar('CREW_RANK_NAMES', $crewRankNames);
        $game->setTemplateVar('CREW_RANK_EXPERTISES', $crewRankExpertises);
        $game->setTemplateVar('POSITIONS', CrewTypeEnum::getOrder());
    }
}
