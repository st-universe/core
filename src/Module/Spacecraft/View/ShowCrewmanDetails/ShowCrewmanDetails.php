<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowCrewmanDetails;

use request;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SkillEnhancementLogRepositoryInterface;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;

final class ShowCrewmanDetails implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_CREWMAN_DETAILS';

    private const int DEFAULT_LIMIT = 5;

    public function __construct(
        private CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private SkillEnhancementLogRepositoryInterface $skillEnhancementLogRepository,
        private UserCrewRankRepositoryInterface $userCrewRankRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $count = request::postInt('count');
        if ($count < 1) {
            $count = self::DEFAULT_LIMIT;
        }

        $game->setMacroInAjaxWindow('html/entityNotAvailable.twig');

        $crewAssignment = $this->crewAssignmentRepository->find(request::indInt('id'));
        $user = $game->getUser();
        if ($crewAssignment === null || $crewAssignment->getCrew()->getUserId() !== $user->getId()) {
            return;
        }

        $game->setPageTitle('Crewman Details');
        $game->setViewTemplate('html/spacecraft/crewmanDetails.twig');
        $game->setTemplateVar('CREW_ASSIGNMENT', $crewAssignment);
        $game->setTemplateVar('COUNT', $count);
        $crewRankNames = [];
        foreach (CrewSkillLevelEnum::cases() as $rank) {
            $crewRankNames[$rank->value] = $this->userCrewRankRepository->getRankName($user, $rank);
        }
        $game->setTemplateVar('CREW_RANK_NAMES', $crewRankNames);
        $skillData = [];
        foreach (CrewTypeEnum::getOrder() as $position) {
            if ($position === CrewTypeEnum::CREWMAN) {
                continue;
            }

            $skillData[$position->value] = [
                'name' => $position->getDescription(),
                'expertise' => 0
            ];
        }
        foreach ($crewAssignment->getCrew()->getSkills() as $skill) {
            $position = $skill->getPosition();
            if (isset($skillData[$position->value])) {
                $skillData[$position->value]['expertise'] = $skill->getExpertise();
            }
        }
        $game->setTemplateVar('CREW_SKILL_RADAR', [
            'skills' => array_values($skillData),
            'ranks' => array_map(
                static fn (CrewSkillLevelEnum $rank): array => [
                    'name' => $crewRankNames[$rank->value],
                    'expertise' => $rank->getNeededExpertise()
                ],
                array_reverse(CrewSkillLevelEnum::cases())
            )
        ]);
        $game->setTemplateVar(
            'LOGS',
            array_slice($this->skillEnhancementLogRepository->getForCrewman($crewAssignment->getCrew()), 0, $count)
        );
    }
}
