<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowAdminDeletePost;

use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;


final class ShowAdminDeletePost implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ADMIN_DELETE_POST';

    public function __construct(
        private ShowAdminDeletePostRequestInterface $showAdminDeletePostRequest,
        private KnPostRepositoryInterface $knPostRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {

        $postId = $this->showAdminDeletePostRequest->getPostId();

        $post = $this->knPostRepository->find($postId);

        if ($post === null) {
            return;
        }

        if (!$game->isAdmin()) {
            return;
        }

        $game->setPageTitle(sprintf(_('KN Beitrag %s löschen'), $post->getId()));
        $game->setMacroInAjaxWindow('html/communication/adminDelete.twig');
        $game->setTemplateVar('POST', $post);
    }
}
