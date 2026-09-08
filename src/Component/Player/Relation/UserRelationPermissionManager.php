<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\RelationPermissionRepositoryInterface;

final class UserRelationPermissionManager
{
    public function __construct(
        private readonly RelationPermissionRepositoryInterface $relationPermissionRepository,
        private readonly UserRelationAccessChecker $accessChecker,
        private readonly UserRelationMessenger $messenger
    ) {}

    public function propose(User $actor, Relation $relation, int $permissions): bool
    {
        if ($relation->isPending() || $relation->isWar() || $relation->hasPendingPermissionChanges()) {
            return false;
        }

        $source = $relation->getSourceParty();
        $recipient = $relation->getRecipientParty();
        $offeredBySource = $this->accessChecker->canRepresentParty($actor, $source);
        if (!$offeredBySource && !$this->accessChecker->canRepresentParty($actor, $recipient)) {
            return false;
        }

        if (!$this->relationPermissionRepository->proposeForRelation($relation, $permissions, $offeredBySource)) {
            return false;
        }

        $this->messenger->sendToParty(
            $offeredBySource ? $recipient : $source,
            sprintf(
                '%s hat eine Rechteänderung für das %s angeboten',
                $this->messenger->describeParty($offeredBySource ? $source : $recipient),
                $relation->getType()->getDescription()
            )
        );

        return true;
    }

    public function accept(User $actor, Relation $relation): bool
    {
        if (
            !$this->mayRespondToChange($actor, $relation)
            || !$this->canRepresentActorParty($actor, $relation)
            || !$this->relationPermissionRepository->acceptPendingForRelation($relation)
        ) {
            return false;
        }

        $this->sendResponse($actor, $relation, 'angenommen');
        return true;
    }

    public function decline(User $actor, Relation $relation): bool
    {
        if (
            !$this->mayRespondToChange($actor, $relation)
            || !$this->canRepresentRelationParty($actor, $relation)
            || !$this->relationPermissionRepository->discardPendingForRelation($relation)
        ) {
            return false;
        }

        $this->sendResponse($actor, $relation, 'abgelehnt');
        return true;
    }

    public function cancel(User $actor, Relation $relation): bool
    {
        if (
            $relation->isPending()
            || !$relation->hasPendingPermissionChanges()
            || !$relation->isPermissionChangeOfferedBy($actor)
            || !$this->canRepresentRelationParty($actor, $relation)
        ) {
            return false;
        }

        return $this->relationPermissionRepository->discardPendingForRelation($relation);
    }

    private function mayRespondToChange(User $actor, Relation $relation): bool
    {
        return !$relation->isPending()
            && $relation->hasPendingPermissionChanges()
            && !$relation->isPermissionChangeOfferedBy($actor);
    }

    private function canRepresentRelationParty(User $actor, Relation $relation): bool
    {
        return $this->accessChecker->canRepresentParty($actor, $relation->getSourceParty())
            || $this->accessChecker->canRepresentParty($actor, $relation->getRecipientParty());
    }

    private function canRepresentActorParty(User $actor, Relation $relation): bool
    {
        return $this->accessChecker->canRepresentParty(
            $actor,
            $relation->isSourceParty($actor) ? $relation->getSourceParty() : $relation->getRecipientParty()
        );
    }

    private function sendResponse(User $actor, Relation $relation, string $response): void
    {
        $actorIsSource = $relation->isSourceParty($actor);
        $actorParty = $actorIsSource ? $relation->getSourceParty() : $relation->getRecipientParty();
        $counterpart = $actorIsSource ? $relation->getRecipientParty() : $relation->getSourceParty();
        $this->messenger->sendToParty(
            $counterpart,
            sprintf(
                '%s hat die Rechteänderung für das %s %s',
                $this->messenger->describeParty($actorParty),
                $relation->getType()->getDescription(),
                $response
            )
        );
    }
}
