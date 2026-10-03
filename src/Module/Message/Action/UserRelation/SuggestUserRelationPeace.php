<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\UserRelation;

use Stu\Component\Player\Relation\UserRelationManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Message\View\ShowContactList\ShowContactList;
use Stu\Orm\Repository\RelationRepositoryInterface;

final class SuggestUserRelationPeace implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SUGGEST_USER_RELATION_PEACE';

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

        if ($relation === null || !$this->userRelationManager->suggestPeace($context->getUser(), $relation)) {
            $context->getInfo()->addInformation('Der Frieden kann nicht angeboten werden');
            return;
        }

        $context->getInfo()->addInformation('Der Frieden wurde angeboten');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
