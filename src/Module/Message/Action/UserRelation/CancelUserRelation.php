<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\UserRelation;

use Stu\Component\Player\Relation\UserRelationManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Message\View\ShowContactList\ShowContactList;
use Stu\Orm\Repository\RelationRepositoryInterface;

final class CancelUserRelation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CANCEL_USER_RELATION';

    public function __construct(
        private readonly UserRelationRequestInterface $userRelationRequest,
        private readonly RelationRepositoryInterface $userRelationRepository,
        private readonly UserRelationManagerInterface $userRelationManager
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowContactList::VIEW_IDENTIFIER);
        $relation = $this->userRelationRepository->find($this->userRelationRequest->getRelationId());

        if ($relation !== null && !$relation->isPending()) {
            if (!$this->userRelationManager->cancelPermissionChange($context->getUser(), $relation)) {
                $context->getInfo()->addInformation('Die Rechteänderung kann nicht zurückgezogen werden');
                return;
            }

            $context->getInfo()->addInformation('Die Rechteänderung wurde zurückgezogen');
            return;
        }

        if ($relation === null || !$this->userRelationManager->cancel($context->getUser(), $relation)) {
            $context->getInfo()->addInformation('Das Abkommen kann nicht aufgelöst werden');
            return;
        }

        $context->getInfo()->addInformation('Das Abkommen wurde aufgelöst');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
