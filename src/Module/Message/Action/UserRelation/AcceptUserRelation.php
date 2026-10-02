<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\UserRelation;

use Stu\Component\Player\Relation\UserRelationManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Message\View\ShowContactList\ShowContactList;
use Stu\Orm\Repository\RelationRepositoryInterface;

final class AcceptUserRelation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ACCEPT_USER_RELATION';

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
            if (!$this->userRelationManager->acceptPermissionChange($context->getUser(), $relation)) {
                $context->getInfo()->addInformation('Die Rechteänderung kann nicht angenommen werden');
                return;
            }

            $context->getInfo()->addInformation('Die Rechteänderung wurde angenommen');
            return;
        }

        if ($relation === null || !$this->userRelationManager->accept($context->getUser(), $relation)) {
            $context->getInfo()->addInformation('Das Angebot kann nicht angenommen werden');
            return;
        }

        $context->getInfo()->addInformation('Das Angebot wurde angenommen');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
