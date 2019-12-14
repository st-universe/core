<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\BoardSettings;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;

final class BoardSettings implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BOARD_SETTINGS';

    private BoardSettingsRequestInterface $boardSettingsRequest;

    private AllianceBoardRepositoryInterface $allianceBoardRepository;

    public function __construct(
        BoardSettingsRequestInterface $boardSettingsRequest,
        AllianceBoardRepositoryInterface $allianceBoardRepository
    ) {
        $this->boardSettingsRequest = $boardSettingsRequest;
        $this->allianceBoardRepository = $allianceBoardRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $board = $this->allianceBoardRepository->find($this->boardSettingsRequest->getBoardId());
        if ($board === null || $board->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Forum bearbeiten'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/alliancemacros.xhtml/board_settings');
        $game->setTemplateVar('BOARD', $board);
    }
}
