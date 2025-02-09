<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowCrewmanDetails;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SkillEnhancementLogRepositoryInterface;

final class ShowCrewmanDetails implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_CREWMAN_DETAILS';

    private const int DEFAULT_LIMIT = 50;

    public function __construct(
        private CrewAssignmentRepositoryInterface $crewAssignmentRepository,
        private SkillEnhancementLogRepositoryInterface $skillEnhancementLogRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $count = request::postInt('count');

        if (!$count || $count < 1) {
            $count = self::DEFAULT_LIMIT;
        }

        $game->setMacroInAjaxWindow('html/entityNotAvailable.twig');

        $crewAssignment = $this->crewAssignmentRepository->find(request::indInt('id'));
        if ($crewAssignment === null) {
            return;
        }

        $crew = $crewAssignment->getCrew();
        if ($crew->getUser() !== $game->getUser()) {
            return;
        }

        $game->setPageTitle('Crewman Details');
        $game->setViewTemplate('html/spacecraft/crewmanDetails.twig');

        $game->setTemplateVar('CREW_ASSIGNMENT', $crewAssignment);
        $game->setTemplateVar('COUNT', $count);
        $game->setTemplateVar('LOGS', $this->skillEnhancementLogRepository->getForCrewman($crew));
    }
}
