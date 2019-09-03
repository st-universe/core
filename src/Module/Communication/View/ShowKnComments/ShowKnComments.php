<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnComments;

use KNPosting;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class ShowKnComments implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_KN_COMMENTS';

    private $showKnCommentsRequest;

    public function __construct(
        ShowKnCommentsRequestInterface $showKnCommentsRequest
    ) {
        $this->showKnCommentsRequest = $showKnCommentsRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $post = new KNPosting($this->showKnCommentsRequest->getKnPostId());

        $list = [];
        foreach ($post->getComments() as $comment) {
            $list[] = new KnCommentTal($comment, $post, $userId);
        }

        $game->setPageTitle(sprintf(_('Kommentare für Beitrag %d'), $post->getId()));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/commmacros.xhtml/kncomments');
        $game->setTemplateVar('POST', $post);
        $game->setTemplateVar('COMMENTS', $list);
    }
}
