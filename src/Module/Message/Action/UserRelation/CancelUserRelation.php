<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\UserRelation;

use Stu\Component\Player\Relation\UserRelationManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\View\ShowContactList\ShowContactList;
use Stu\Orm\Repository\UserRelationRepositoryInterface;

final class CancelUserRelation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CANCEL_USER_RELATION';

    public function __construct(
        private readonly UserRelationRequestInterface $userRelationRequest,
        private readonly UserRelationRepositoryInterface $userRelationRepository,
        private readonly UserRelationManagerInterface $userRelationManager
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowContactList::VIEW_IDENTIFIER);
        $relation = $this->userRelationRepository->find($this->userRelationRequest->getRelationId());

        if ($relation === null || !$this->userRelationManager->cancel($game->getUser(), $relation)) {
            $game->getInfo()->addInformation('Das Abkommen kann nicht aufgelöst werden');
            return;
        }

        $game->getInfo()->addInformation('Das Abkommen wurde aufgelöst');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
