<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use InvalidArgumentException;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\RelationPermissionRepositoryInterface;
use Stu\Orm\Repository\RelationRepositoryInterface;

final class UserRelationCreator
{
    public function __construct(
        private readonly RelationRepositoryInterface $relationRepository,
        private readonly RelationPermissionRepositoryInterface $relationPermissionRepository
    ) {}

    public function create(
        User|Alliance $source,
        User|Alliance $recipient,
        AllianceRelationTypeEnum $type,
        int $date = 0,
        int $permissions = 0
    ): Relation {
        $relation = $this->relationRepository->prototype()->setType($type)->setDate($date);

        if ($source instanceof User) {
            $relation->setSourceUser($source);
        } else {
            $relation->setSourceAlliance($source);
        }

        if ($recipient instanceof User) {
            $relation->setRecipientUser($recipient);
        } else {
            $relation->setRecipientAlliance($recipient);
        }

        $this->relationRepository->save($relation);
        $this->relationPermissionRepository->replaceForRelation($relation, $permissions, $type);

        return $relation;
    }

    /** @return array<int, Relation> */
    public function getByParties(User|Alliance $source, User|Alliance $recipient): array
    {
        if ($source instanceof User && $recipient instanceof User) {
            return $this->relationRepository->getByUserPair($source, $recipient);
        }
        if ($source instanceof Alliance && $recipient instanceof Alliance) {
            throw new InvalidArgumentException('Relations between alliances are not allowed');
        }

        $alliance = $source instanceof Alliance ? $source : $recipient;
        $user = $source instanceof User ? $source : $recipient;

        return $this->relationRepository->getByAllianceAndUserPair($alliance, $user);
    }
}
