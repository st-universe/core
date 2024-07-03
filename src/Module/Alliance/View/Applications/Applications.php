<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Applications;

use Override;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;

final class Applications implements ViewControllerInterface
{
    /**
     * @var string
     */
    public const string VIEW_IDENTIFIER = 'SHOW_APPLICATIONS';

    public function __construct(private AllianceActionManagerInterface $allianceActionManager, private AllianceJobRepositoryInterface $allianceJobRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        if ($alliance === null) {
            throw new AccessViolation();
        }

        if (!$this->allianceActionManager->mayEdit($alliance, $game->getUser())) {
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
                $alliance->getId(),
                AllianceEnum::ALLIANCE_JOBS_PENDING
            )
        );
    }
}
