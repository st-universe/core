<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\BoardSettings;

use Override;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;

final class BoardSettings implements ViewControllerInterface
{
    /**
     * @var string
     */
    public const string VIEW_IDENTIFIER = 'SHOW_BOARD_SETTINGS';

    public function __construct(private BoardSettingsRequestInterface $boardSettingsRequest, private AllianceBoardRepositoryInterface $allianceBoardRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $board = $this->allianceBoardRepository->find($this->boardSettingsRequest->getBoardId());
        if ($board === null || $board->getAllianceId() !== $alliance->getId()) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Forum bearbeiten'));
        $game->setMacroInAjaxWindow('html/alliance/allianceboardsettings.twig');
        $game->setTemplateVar('BOARD', $board);
    }
}
