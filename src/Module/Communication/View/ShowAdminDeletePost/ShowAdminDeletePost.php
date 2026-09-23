<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowAdminDeletePost;

use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\GameUserRoleCheckerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class ShowAdminDeletePost implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ADMIN_DELETE_POST';

    public function __construct(
        private ShowAdminDeletePostRequestInterface $showAdminDeletePostRequest,
        private KnPostRepositoryInterface $knPostRepository,
        private GameUserRoleCheckerInterface $gameUserRoleChecker
    ) {}

    #[\Override]
    public function handle(ViewControllerContext $game): void
    {

        $postId = $this->showAdminDeletePostRequest->getPostId();

        $post = $this->knPostRepository->find($postId);

        if ($post === null) {
            return;
        }

        if (!$this->gameUserRoleChecker->isAdmin()) {
            return;
        }

        $game->setPageTitle(sprintf(_('KN Beitrag %s löschen'), $post->getId()));
        $game->setMacroInAjaxWindow('html/communication/adminDelete.twig');
        $game->setTemplateVar('POST', $post);
    }
}
