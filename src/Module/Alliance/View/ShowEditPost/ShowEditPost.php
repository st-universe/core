<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\ShowEditPost;

use request;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceBoardPostRepositoryInterface;

final class ShowEditPost implements ViewControllerInterface
{
    /**
     * @var string
     */
    public const VIEW_IDENTIFIER = 'SHOW_EDIT_ALLY_POST';

    private AllianceBoardPostRepositoryInterface $allianceBoardPostRepository;

    public function __construct(
        AllianceBoardPostRepositoryInterface $allianceBoardPostRepository
    ) {
        $this->allianceBoardPostRepository = $allianceBoardPostRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $post = $this->allianceBoardPostRepository->find(request::getIntFatal('pid'));
        if ($post === null) {
            return;
        }

        $board = $post->getBoard();
        $topic = $post->getTopic();
        if ($board->getAllianceId() !== $alliance->getId()) {
            throw new AccessViolation();
        }

        $game->setTemplateFile('html/editallypost.xhtml');
        $game->appendNavigationPart(sprintf('alliance.php?id=%d', $alliance->getId()), _('Allianz'));
        $game->appendNavigationPart('alliance.php?SHOW_BOARDS=1', _('Forum'));
        $game->appendNavigationPart(sprintf('alliance.php?SHOW_TOPIC=1&bid=%d&tid=%d', $board->getId(), $topic->getId()), _('Allianz'));
        $game->appendNavigationPart(
            sprintf('alliance.php?%s=1&pid=%d', static::VIEW_IDENTIFIER, $post->getId()),
            _('Beitrag bearbeiten')
        );
        $game->setPageTitle(_('Beitrag bearbeiten'));

        $game->setTemplateVar('POST', $post);
    }
}
