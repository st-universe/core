<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\Alliance\Enum\RelationPermissionDirectionEnum;
use Stu\Component\Alliance\Enum\RelationPermissionEnum;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\RelationPermission;

/**
 * @extends ObjectRepository<RelationPermission>
 *
 * @method null|RelationPermission find(integer $id)
 */
interface RelationPermissionRepositoryInterface extends ObjectRepository
{
    public function prototype(): RelationPermission;

    public function save(RelationPermission $permission): void;

    public function grantForRelation(Relation $relation, RelationPermissionEnum $permission): void;

    public function replaceForRelation(
        Relation $relation,
        int $permissions,
        AllianceRelationTypeEnum $type,
        RelationPermissionDirectionEnum $direction = RelationPermissionDirectionEnum::MUTUAL
    ): void;

    public function replaceForContact(Contact $contact, int $permissions): void;

    public function hasSamePermissions(Relation $relation, int $permissions): bool;

    public function proposeForRelation(
        Relation $relation,
        int $permissions,
        bool $offeredBySource
    ): bool;

    public function acceptPendingForRelation(Relation $relation): bool;

    public function discardPendingForRelation(Relation $relation): bool;

    public function deleteByRelation(Relation $relation): void;

    /** @param array<int, Relation> $relations */
    public function deleteByRelations(array $relations): void;

    public function deleteByContact(Contact $contact): void;

    /** @param array<int, Contact> $contacts */
    public function deleteByContacts(array $contacts): void;
}
