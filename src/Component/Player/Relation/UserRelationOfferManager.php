<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\RelationPermissionRepositoryInterface;
use Stu\Orm\Repository\RelationRepositoryInterface;

final class UserRelationOfferManager
{
    public function __construct(
        private readonly RelationRepositoryInterface $relationRepository,
        private readonly RelationPermissionRepositoryInterface $relationPermissionRepository,
        private readonly UserRelationAccessChecker $accessChecker,
        private readonly UserRelationCreator $relationCreator,
        private readonly UserRelationMessenger $messenger,
        private readonly UserRelationHistory $history
    ) {}

    public function create(
        User $actor,
        User|Alliance $source,
        User|Alliance $recipient,
        AllianceRelationTypeEnum $type,
        int $permissions
    ): ?Relation {
        if (!$this->mayCreate($actor, $source, $recipient, $type)) {
            return null;
        }

        $relations = $this->relationCreator->getByParties($source, $recipient);
        if ($this->hasDuplicateRelation($relations, $type, $permissions)) {
            return null;
        }

        if ($type === AllianceRelationTypeEnum::WAR) {
            return $this->declareWar($actor, $source, $recipient, $relations, $permissions);
        }

        if ($this->hasTooManyPendingRelations($relations)) {
            return null;
        }

        return $this->offerRelation($source, $recipient, $type, $permissions);
    }

    private function mayCreate(
        User $actor,
        User|Alliance $source,
        User|Alliance $recipient,
        AllianceRelationTypeEnum $type
    ): bool {
        return $this->accessChecker->canCreateForParty($actor, $source)
            && $this->accessChecker->hasValidParties($source, $recipient)
            && $type !== AllianceRelationTypeEnum::PEACE;
    }

    /** @param array<int, Relation> $relations */
    private function hasDuplicateRelation(
        array $relations,
        AllianceRelationTypeEnum $type,
        int $permissions
    ): bool {
        foreach ($relations as $relation) {
            if (
                $relation->getType() === $type
                && (
                    $relation->isPending()
                    || $this->relationPermissionRepository->hasSamePermissions($relation, $permissions)
                )
            ) {
                return true;
            }
        }

        return false;
    }

    /** @param array<int, Relation> $relations */
    private function declareWar(
        User $actor,
        User|Alliance $source,
        User|Alliance $recipient,
        array $relations,
        int $permissions
    ): Relation {
        foreach ($relations as $relation) {
            $this->relationRepository->delete($relation);
        }

        $relation = $this->relationCreator->create(
            $source,
            $recipient,
            AllianceRelationTypeEnum::WAR,
            time(),
            $permissions
        );
        $text = sprintf(
            '%s hat %s den Krieg erklärt',
            $this->messenger->describeParty($source),
            $this->messenger->describeParty($recipient)
        );
        $this->messenger->sendToParty($recipient, $text);
        $this->history->add($relation, $actor->getId(), $text);

        return $relation;
    }

    /** @param array<int, Relation> $relations */
    private function hasTooManyPendingRelations(array $relations): bool
    {
        return count(array_filter($relations, static fn (Relation $relation): bool => $relation->isPending())) >= 2;
    }

    private function offerRelation(
        User|Alliance $source,
        User|Alliance $recipient,
        AllianceRelationTypeEnum $type,
        int $permissions
    ): Relation {
        $relation = $this->relationCreator->create($source, $recipient, $type, 0, $permissions);
        $this->messenger->sendToParty(
            $recipient,
            sprintf(
                '%s hat %s ein %s angeboten',
                $this->messenger->describeParty($source),
                $this->messenger->describeParty($recipient),
                $type->getDescription()
            )
        );

        return $relation;
    }

}
