<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\UserRelation;

use Stu\Component\Player\Relation\UserRelationManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Message\View\ShowContactList\ShowContactList;
use Stu\Orm\Repository\RelationRepositoryInterface;

final class DeclineUserRelation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DECLINE_USER_RELATION';

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
            if (!$this->userRelationManager->declinePermissionChange($context->getUser(), $relation)) {
                $context->getInfo()->addInformation('Die Rechteänderung kann nicht abgelehnt werden');
                return;
            }

            $context->getInfo()->addInformation('Die Rechteänderung wurde abgelehnt');
            return;
        }

        if ($relation === null || !$this->userRelationManager->decline($context->getUser(), $relation)) {
            $context->getInfo()->addInformation('Das Angebot kann nicht abgelehnt werden');
            return;
        }

        $context->getInfo()->addInformation('Das Angebot wurde abgelehnt');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
