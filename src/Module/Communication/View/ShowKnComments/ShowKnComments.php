<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnComments;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class ShowKnComments implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_KN_COMMENTS';

    private $showKnCommentsRequest;

    private $knPostRepository;

    public function __construct(
        ShowKnCommentsRequestInterface $showKnCommentsRequest,
        KnPostRepositoryInterface $knPostRepository
    ) {
        $this->showKnCommentsRequest = $showKnCommentsRequest;
        $this->knPostRepository = $knPostRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $post = $this->knPostRepository->find($this->showKnCommentsRequest->getKnPostId());

        if ($post === null) {
            return;
        }

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
