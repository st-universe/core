<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\BoardSettings;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;

final class BoardSettings implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BOARD_SETTINGS';

    private BoardSettingsRequestInterface $boardSettingsRequest;

    private AllianceBoardRepositoryInterface $allianceBoardRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        BoardSettingsRequestInterface $boardSettingsRequest,
        AllianceBoardRepositoryInterface $allianceBoardRepository,
        LoggerUtilInterface $loggerUtil
    ) {
        $this->boardSettingsRequest = $boardSettingsRequest;
        $this->allianceBoardRepository = $allianceBoardRepository;
        $this->loggerUtil = $loggerUtil;
    }

    public function handle(GameControllerInterface $game): void
    {
        $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);

        $alliance = $game->getUser()->getAlliance();

        $board = $this->allianceBoardRepository->find($this->boardSettingsRequest->getBoardId());
        if ($board === null || $board->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Forum bearbeiten'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/alliancemacros.xhtml/board_settings');
        $this->loggerUtil->log(sprintf('boardname: %s', $board->getName()));
        $game->setTemplateVar('BOARD', $board);
        $game->setTemplateVar('BOARD_ID', $board->getId());
    }
}
