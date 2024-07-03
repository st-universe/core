<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeletePost;

use Override;
use request;
use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\View\Board\Board;
use Stu\Module\Alliance\View\Topic\Topic;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\AllianceBoardPostInterface;
use Stu\Orm\Repository\AllianceBoardPostRepositoryInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class DeletePost implements ActionControllerInterface
{
    /**
     * @var string
     */
    public const string ACTION_IDENTIFIER = 'B_DEL_POSTING';

    public function __construct(private AllianceBoardPostRepositoryInterface $allianceBoardPostRepository, private AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        /** @var AllianceBoardPostInterface $post */
        $post = $this->allianceBoardPostRepository->find(request::getIntFatal('pid'));
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

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
