<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\UnsetTopicSticky;

use request;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceForumPermission;
use Stu\Module\Alliance\View\Topic\Topic;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;

final class UnsetTopicSticky implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_UNSET_STICKY';

    public function __construct(
        private UnsetTopicStickyRequestInterface $unsetTopicStickyRequest,
        private AllianceBoardTopicRepositoryInterface $allianceBoardTopicRepository,
        private AllianceForumPermission $allianceForumPermission
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        if (!request::isPost()) {
            return;
        }

        $alliance = $context->getUser()->getAlliance();

        $topic = $this->allianceBoardTopicRepository->find($this->unsetTopicStickyRequest->getTopicId());
        if ($topic === null || $topic->getAlliance()->getId() !== $alliance?->getId()) {
            throw new AccessViolationException();
        }

        $this->allianceForumPermission->assertCanManageForum($context->getUser(), $topic->getAlliance());

        $topic->setSticky(false);

        $this->allianceBoardTopicRepository->save($topic);

        $context->getInfo()->addInformation(_('Die Markierung des Themas wurde entfernt'));

        $context->setView(Topic::VIEW_IDENTIFIER);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
