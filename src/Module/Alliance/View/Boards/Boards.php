<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Boards;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;

final class Boards implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BOARDS';

    private $allianceBoardRepository;

    public function __construct(
        AllianceBoardRepositoryInterface $allianceBoardRepository
    ) {
        $this->allianceBoardRepository = $allianceBoardRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $game->setPageTitle(_('Allianzforum'));
        $game->appendNavigationPart(
            'alliance.php',
            _('Allianz')
        );
        $game->appendNavigationPart(
            'alliance.php?SHOW_BOARDS=1',
            _('Forum')
        );
        $game->setTemplateFile('html/allianceboard.xhtml');

        $game->setTemplateVar(
            'BOARDS',
            $this->allianceBoardRepository->getByAlliance((int) $alliance->getId())
        );
        $game->setTemplateVar(
            'EDITABLE',
            $alliance->currentUserMayEdit()
        );
    }
}
