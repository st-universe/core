<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Applications;

use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceApplicationRepositoryInterface;

final class Applications implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_APPLICATIONS';

    public function __construct(
        private AllianceActionManagerInterface $allianceActionManager,
        private AllianceApplicationRepositoryInterface $allianceApplicationRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException("user not in alliance");
        }

        if (!$this->allianceActionManager->mayManageApplications($alliance, $game->getUser())) {
            throw new AccessViolationException();
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
        $game->setViewTemplate('html/alliance/allianceapplications.twig');

        $applications = $this->allianceApplicationRepository->getByAlliance($alliance->getId());

        $game->setTemplateVar('APPLICATIONS', $applications);
    }
}
