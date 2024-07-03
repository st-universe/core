<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Board;

use Override;
use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\AllianceBoardInterface;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class Board implements ViewControllerInterface
{
    /**
     * @var string
     */
    public const string VIEW_IDENTIFIER = 'SHOW_BOARD';

    public function __construct(private BoardRequestInterface $boardRequest, private AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository, private AllianceBoardRepositoryInterface $allianceBoardRepository, private AllianceActionManagerInterface $allianceActionManager)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        if ($alliance === null) {
            throw new AccessViolation();
        }

        $allianceId = $alliance->getId();

        /** @var AllianceBoardInterface $board */
        $board = $this->allianceBoardRepository->find($this->boardRequest->getBoardId());

        if ($board === null || $board->getAllianceId() !== $allianceId) {
            throw new AccessViolation();
        }

        $boardId = $board->getId();

        $game->setPageTitle(_('Allianzforum'));

        $game->appendNavigationPart(
            'alliance.php',
            _('Allianz')
        );
        $game->appendNavigationPart(
            sprintf('alliance.php?SHOW_BOARDS=1&id=%d', $boardId),
            _('Forum')
        );
        $game->appendNavigationPart(
            sprintf(
                'alliance.php?SHOW_BOARD=1&bid=%d',
                $boardId,
            ),
            $board->getName()
        );
        $game->setTemplateFile('html/allianceboardtopics.xhtml');
        $game->setTemplateVar(
            'TOPICS',
            $this->allianceBoardTopicRepository->getByBoardIdOrdered($boardId)
        );
        $game->setTemplateVar(
            'EDITABLE',
            $this->allianceActionManager->mayEdit($alliance, $game->getUser())
        );
        $game->setTemplateVar('BOARD_ID', $boardId);
    }
}
