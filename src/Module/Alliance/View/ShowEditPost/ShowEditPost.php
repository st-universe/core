<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\ShowEditPost;

use Override;
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
    public const string VIEW_IDENTIFIER = 'SHOW_EDIT_ALLY_POST';

    public function __construct(private AllianceBoardPostRepositoryInterface $allianceBoardPostRepository)
    {
    }

    #[Override]
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

        $game->setViewTemplate('html/alliance/editallypost.twig');
        $game->appendNavigationPart(sprintf('alliance.php?id=%d', $alliance->getId()), _('Allianz'));
        $game->appendNavigationPart('alliance.php?SHOW_BOARDS=1', _('Forum'));
        $game->appendNavigationPart(sprintf('alliance.php?SHOW_TOPIC=1&bid=%d&tid=%d', $board->getId(), $topic->getId()), _('Allianz'));
        $game->appendNavigationPart(
            sprintf('alliance.php?%s=1&pid=%d', self::VIEW_IDENTIFIER, $post->getId()),
            _('Beitrag bearbeiten')
        );
        $game->setPageTitle(_('Beitrag bearbeiten'));

        $game->setTemplateVar('POST', $post);
    }
}
