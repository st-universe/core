<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\BoardSettings;

use AccessViolation;
use AllianceBoard;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class BoardSettings implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BOARD_SETTINGS';

    private $boardSettingsRequest;

    public function __construct(
        BoardSettingsRequestInterface $boardSettingsRequest
    ) {
        $this->boardSettingsRequest = $boardSettingsRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $board = new AllianceBoard($this->boardSettingsRequest->getBoardId());
        if ($board->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Forum bearbeiten'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setAjaxMacro('html/alliancemacros.xhtml/board_settings');
        $game->setTemplateVar('BOARD', $board);
    }
}
