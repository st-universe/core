<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\NewTopic;

use Override;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\AllianceBoard;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;

final class NewTopic implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_NEW_TOPIC';

    public function __construct(
        private NewTopicRequestInterface $newTopicRequest,
        private AllianceBoardRepositoryInterface $allianceBoardRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        /** @var AllianceBoard $board */
        $board = $this->allianceBoardRepository->find($this->newTopicRequest->getBoardId());
        if ($board === null || $board->getAllianceId() !== $alliance->getId()) {
            throw new AccessViolationException();
        }

        $boardId = $board->getId();

        $game->setPageTitle(_('Allianzforum'));

        $game->appendNavigationPart(
            'alliance.php',
            _('Allianz')
        );
        $game->appendNavigationPart(
            'alliance.php?SHOW_BOARDS=1',
            _('Forum')
        );
        $game->appendNavigationPart(
            sprintf(
                'alliance.php?SHOW_BOARD=1&boardid=%d',
                $boardId
            ),
            $board->getName()
        );
        $game->appendNavigationPart(
            sprintf(
                'alliance.php?SHOW_NEW_TOPIC=1&boardid=%d',
                $boardId
            ),
            _('Thema erstellen')
        );

        $game->setViewTemplate('html/alliance/allianceboardcreatetopic.twig');
        $game->setTemplateVar('BOARD_ID', $boardId);
    }
}
