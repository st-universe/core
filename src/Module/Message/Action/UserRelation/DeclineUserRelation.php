<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\UserRelation;

use Stu\Component\Player\Relation\UserRelationManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
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
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowContactList::VIEW_IDENTIFIER);
        $relation = $this->userRelationRepository->find($this->userRelationRequest->getRelationId());

        if ($relation !== null && !$relation->isPending()) {
            if (!$this->userRelationManager->declinePermissionChange($game->getUser(), $relation)) {
                $game->getInfo()->addInformation('Die Rechteänderung kann nicht abgelehnt werden');
                return;
            }

            $game->getInfo()->addInformation('Die Rechteänderung wurde abgelehnt');
            return;
        }

        if ($relation === null || !$this->userRelationManager->decline($game->getUser(), $relation)) {
            $game->getInfo()->addInformation('Das Angebot kann nicht abgelehnt werden');
            return;
        }

        $game->getInfo()->addInformation('Das Angebot wurde abgelehnt');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
