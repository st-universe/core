<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeletePost;

use AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Alliance\View\Board\Board;
use Stu\Module\Alliance\View\Topic\Topic;
use Stu\Orm\Entity\AllianceBoardPostInterface;
use Stu\Orm\Repository\AllianceBoardPostRepositoryInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class DeletePost implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_DEL_POSTING';

    private $deletePostRequest;

    private $allianceBoardPostRepository;

    private $allianceBoardTopicRepository;

    public function __construct(
        DeletePostRequestInterface $deletePostRequest,
        AllianceBoardPostRepositoryInterface $allianceBoardPostRepository,
        AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository
    ) {
        $this->deletePostRequest = $deletePostRequest;
        $this->allianceBoardPostRepository = $allianceBoardPostRepository;
        $this->allianceBoardTopicRepository = $allianceBoardTopicRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        /** @var AllianceBoardPostInterface $post */
        $post = $this->allianceBoardPostRepository->find($this->deletePostRequest->getPostId());
        if ($post === null) {
            return;
        }
        if ($post->getBoard()->getAllianceId() !== $alliance->getId()) {
            throw new AccessViolation();
        }

        if ($post->getTopic()->getPostCount() == 1) {
            $game->setView(Board::VIEW_IDENTIFIER);

            $this->allianceBoardTopicRepository->delete($post->getTopic());

            $game->addInformation(_('Das Thema wurde gelöscht'));
            return;
        }
        $game->setView(Topic::VIEW_IDENTIFIER);

        $this->allianceBoardPostRepository->delete($post);

        $game->addInformation(_('Der Beitrag wurde gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
