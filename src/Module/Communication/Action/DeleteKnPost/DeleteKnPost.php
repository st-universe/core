<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeleteKnPost;

use AccessViolation;
use KNPosting;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class DeleteKnPost implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEL_KN';

    private $deleteKnPostRequest;

    public function __construct(
        DeleteKnPostRequestInterface $deleteKnPostRequest
    ) {
        $this->deleteKnPostRequest = $deleteKnPostRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $post = new KNPosting($this->deleteKnPostRequest->getPostId());
        if ($post->getUserId() != $userId) {
            throw new AccessViolation();
        }
        if (!$post->isEditAble()) {
            $game->addInformation(_('Dieser Beitrag kann nicht editiert werden'));
            return;
        }

        // @todo foreign key
        //KnComment::truncate('WHERE post_id=' . $post->getId());

        $post->deleteFromDatabase();

        $game->addInformation(_('Der Beitrag wurde gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
