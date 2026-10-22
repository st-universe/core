<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\BoardSettings;

use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceForumPermission;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;

final class BoardSettings implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_BOARD_SETTINGS';

    public function __construct(
        private BoardSettingsRequestInterface $boardSettingsRequest,
        private AllianceBoardRepositoryInterface $allianceBoardRepository,
        private AllianceForumPermission $allianceForumPermission
    ) {}

    #[\Override]
    public function handle(ViewControllerContext $game): void
    {
        $alliance = $game->getUser()->getAlliance();
        if ($alliance === null) {
            throw new AccessViolationException("user not in alliance");
        }

        $board = $this->allianceBoardRepository->find($this->boardSettingsRequest->getBoardId());
        if ($board === null || $board->getAlliance()->getId() !== $alliance->getId()) {
            throw new AccessViolationException();
        }

        $this->allianceForumPermission->assertCanManageForum($game->getUser(), $board->getAlliance());

        $game->setPageTitle(_('Forum bearbeiten'));
        $game->setMacroInAjaxWindow('html/alliance/allianceboardsettings.twig');
        $game->setTemplateVar('BOARD', $board);
    }
}
