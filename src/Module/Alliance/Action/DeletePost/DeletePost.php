<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\DeletePost;

use request;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\View\Board\Board;
use Stu\Module\Alliance\View\Topic\Topic;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\AllianceBoardPostRepositoryInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class DeletePost implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DEL_POSTING';

    public function __construct(
        private AllianceBoardPostRepositoryInterface $allianceBoardPostRepository,
        private AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $alliance = $context->getUser()->getAlliance();

        $post = $this->allianceBoardPostRepository->find(request::getIntFatal('pid'));
        if ($post === null) {
            return;
        }

        if ($post->getBoard()->getAlliance()->getId() !== $alliance?->getId()) {
            throw new AccessViolationException();
        }

        $postcount = $post->getTopic()->getPostCount();
        $this->allianceBoardPostRepository->delete($post);

        if ($postcount == 1) {
            $context->setView(Board::VIEW_IDENTIFIER);

            $this->allianceBoardTopicRepository->delete($post->getTopic());

            $context->getInfo()->addInformation(_('Das Thema wurde gelöscht'));
            return;
        }

        $context->setView(Topic::VIEW_IDENTIFIER);


        $context->getInfo()->addInformation(_('Der Beitrag wurde gelöscht'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
