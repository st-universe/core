<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Applications;

use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceApplicationRepositoryInterface;

final class Applications implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_APPLICATIONS';

    public function __construct(
        private AllianceJobManagerInterface $allianceJobManager,
        private AllianceApplicationRepositoryInterface $allianceApplicationRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException("user not in alliance");
        }

        if (!$this->allianceJobManager->hasUserPermission($game->getUser(), $alliance, AllianceJobPermissionEnum::MANAGE_APPLICATIONS)) {
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
