<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnComments;

use Override;
use Noodlehaus\ConfigInterface;
use Stu\Module\Communication\Action\PostKnComment\PostKnComment;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class ShowKnComments implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_KN_COMMENTS';

    public function __construct(private ShowKnCommentsRequestInterface $showKnCommentsRequest, private ConfigInterface $config, private KnPostRepositoryInterface $knPostRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        /** @var KnPostInterface $post */
        $post = $this->knPostRepository->find($this->showKnCommentsRequest->getKnPostId());

        if ($post === null) {
            return;
        }

        $list = [];
        foreach ($post->getComments() as $comment) {
            if (!$comment->isDeleted()) {
                $list[] = new KnCommentTal(
                    $this->config,
                    $comment,
                    $user
                );
            }
        }

        $game->setPageTitle(sprintf(_('Kommentare für Beitrag %d'), $post->getId()));
        $game->setMacroInAjaxWindow('html/commmacros.xhtml/kncomments');
        $game->setTemplateVar('POST', $post);
        $game->setTemplateVar('COMMENTS', $list);
        $game->setTemplateVar('CHARLIMIT', PostKnComment::CHARACTER_LIMIT);
    }
}
