<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnArchiveComments;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\KnCommentArchivRepositoryInterface;
use Stu\Orm\Repository\KnPostArchivRepositoryInterface;

final class ShowKnArchiveComments implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_KN_ARCHIVE_COMMENTS';

    public function __construct(
        private ShowKnArchiveCommentsRequestInterface $showKnArchiveCommentsRequest,
        private KnPostArchivRepositoryInterface $knPostArchivRepository,
        private KnCommentArchivRepositoryInterface $knCommentArchivRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $postId = $this->showKnArchiveCommentsRequest->getKnPostId();

        $game->setMacroInAjaxWindow('html/communication/knArchiveComments.twig');
        $post = $this->knPostArchivRepository->findByFormerId($postId);
        if ($post === null) {
            return;
        }


        $comments = $this->knCommentArchivRepository->getByPostFormerId($postId);

        $game->setTemplateVar('POST', $post);
        $game->setTemplateVar('COMMENTS', $comments);
        $game->setTemplateVar('CHARLIMIT', 500);
    }
}
