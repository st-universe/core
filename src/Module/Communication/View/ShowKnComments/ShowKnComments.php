<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnComments;

use Noodlehaus\ConfigInterface;
use Stu\Component\Player\Settings\UserSettingsProviderInterface;
use Stu\Module\Communication\Action\PostKnComment\PostKnComment;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class ShowKnComments implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_KN_COMMENTS';

    public function __construct(
        private readonly KnPostRepositoryInterface $knPostRepository,
        private readonly UserSettingsProviderInterface $userSettingsProvider,
        private readonly ShowKnCommentsRequestInterface $showKnCommentsRequest,
        private readonly ConfigInterface $config,
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $post = $this->knPostRepository->find($this->showKnCommentsRequest->getKnPostId());
        if ($post === null) {
            return;
        }

        $list = [];
        foreach ($post->getComments() as $comment) {
            if (!$comment->isDeleted()) {
                $list[] = new KnCommentWrapper(
                    $this->config,
                    $this->userSettingsProvider,
                    $comment,
                    $user
                );
            }
        }

        $game->setPageTitle(sprintf(_('Kommentare für Beitrag %d'), $post->getId()));
        $game->setMacroInAjaxWindow('html/communication/knComments.twig');
        $game->setTemplateVar('POST', $post);
        $game->setTemplateVar('COMMENTS', $list);
        $game->setTemplateVar('CHARLIMIT', PostKnComment::CHARACTER_LIMIT);
    }
}
