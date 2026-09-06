<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\UserRelation;

use Stu\Component\Player\Relation\UserRelationManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\View\ShowContactList\ShowContactList;
use Stu\Orm\Repository\RelationRepositoryInterface;

final class ProposeUserRelationPermissions implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_PROPOSE_USER_RELATION_PERMISSIONS';

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
        if (
            $relation === null
            || !$this->userRelationManager->proposePermissionChange(
                $game->getUser(),
                $relation,
                $this->userRelationRequest->getPermissions()
            )
        ) {
            $game->getInfo()->addInformation('Die Rechte können nicht geändert werden');
            return;
        }

        $game->getInfo()->addInformation('Die Rechteänderung wurde angeboten');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
