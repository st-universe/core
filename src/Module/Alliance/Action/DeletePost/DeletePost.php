<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeletePost;

use AccessViolation;
use AlliancePost;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Board\Board;
use Stu\Module\Alliance\View\Topic\Topic;

final class DeletePost implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_DEL_POSTING';

    private $deletePostRequest;

    public function __construct(
        DeletePostRequestInterface $deletePostRequest
    ) {
        $this->deletePostRequest = $deletePostRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        $post = new AlliancePost($this->deletePostRequest->getPostId());
        if ($post->getAllianceId() != $alliance->getId()) {
            throw new AccessViolation();
        }

        if ($post->getTopic()->getPostCount() == 1) {
            $game->setView(Board::VIEW_IDENTIFIER);

            $post->getTopic()->deepDelete();

            $game->addInformation(_('Das Thema wurde gelöscht'));
            return;
        }
        $game->setView(Topic::VIEW_IDENTIFIER);

        $post->deleteFromDatabase();

        $game->addInformation(_('Der Beitrag wurde gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
