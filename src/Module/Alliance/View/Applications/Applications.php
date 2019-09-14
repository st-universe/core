<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Applications;

use AccessViolation;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;

final class Applications implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_APPLICATIONS';

    private $allianceActionManager;

    private $allianceJobRepository;

    public function __construct(
        AllianceActionManagerInterface $allianceActionManager,
        AllianceJobRepositoryInterface $allianceJobRepository
    ) {
        $this->allianceActionManager = $allianceActionManager;
        $this->allianceJobRepository = $allianceJobRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        if (!$this->allianceActionManager->mayEdit((int) $alliance->getId(), $game->getUser()->getId())) {
            throw new AccessViolation();
        }
        $game->setPageTitle(_('Allianz anzeigen'));
        $game->appendNavigationPart(
            'alliance.php',
            _('Allianz')
        );
        $game->appendNavigationPart(
            'alliance.php?SHOW_APPLICATIONS=1',
            _('Bewerbungen')
        );
        $game->setTemplateFile('html/allianceapplications.xhtml');
        $game->setTemplateVar(
            'APPLICATIONS',
			$this->allianceJobRepository->getByAllianceAndType(
                (int) $alliance->getId(),
                ALLIANCE_JOBS_PENDING
            )
        );
    }
}
