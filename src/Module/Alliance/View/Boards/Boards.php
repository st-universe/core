<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Boards;

use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;

final class Boards implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_BOARDS';

    public function __construct(private AllianceBoardRepositoryInterface $allianceBoardRepository, private AllianceActionManagerInterface $allianceActionManager)
    {
    }

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        if ($alliance === null) {
            throw new AccessViolationException("user not in alliance");
        }

        $allianceId = $alliance->getId();

        $game->setPageTitle(_('Allianzforum'));
        $game->appendNavigationPart(
            'alliance.php',
            _('Allianz')
        );
        $game->appendNavigationPart(
            'alliance.php?SHOW_BOARDS=1',
            _('Forum')
        );
        $game->setViewTemplate('html/alliance/allianceboard.twig');

        $game->setTemplateVar(
            'BOARDS',
            $this->allianceBoardRepository->getByAlliance($allianceId)
        );
        $game->setTemplateVar(
            'EDITABLE',
            $this->allianceActionManager->mayEdit($alliance, $game->getUser())
        );
    }
}
