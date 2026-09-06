<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\Alliance\Enum\RelationPermissionDirectionEnum;
use Stu\Component\Alliance\Enum\RelationPermissionEnum;
use Stu\Orm\Entity\Contact;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\RelationPermission;

final class RelationPermissionRepository extends EntityRepository implements
    RelationPermissionRepositoryInterface
{
    public function prototype(): RelationPermission
    {
        return new RelationPermission();
    }

    public function save(RelationPermission $permission): void
    {
        $permission->validateOwner();
        $this->getEntityManager()->persist($permission);
    }

    public function grantForRelation(Relation $relation, RelationPermissionEnum $permission): void
    {
        if (!$permission->isAvailableFor($relation->getType()) || $relation->hasPermission($permission)) {
            return;
        }

        $relationPermission = $this->prototype()->setRelation($relation)->setPermission($permission);
        $relation->addRelationPermission($relationPermission);
        $this->save($relationPermission);
    }

    public function replaceForRelation(
        Relation $relation,
        int $permissions,
        AllianceRelationTypeEnum $type,
        RelationPermissionDirectionEnum $direction = RelationPermissionDirectionEnum::MUTUAL
    ): void {
        $this->deleteByRelation($relation);

        $permissions = RelationPermissionEnum::sanitize($permissions, $type);
        foreach (RelationPermissionEnum::cases() as $permission) {
            if (($permissions & $permission->value) === 0) {
                continue;
            }

            $relationPermission = $this
                ->prototype()
                ->setRelation($relation)
                ->setPermission($permission)
                ->setDirection($direction);
            $relation->addRelationPermission($relationPermission);
            $this->save($relationPermission);
        }
    }

    public function replaceForContact(Contact $contact, int $permissions): void
    {
        $this->deleteByContact($contact);
        $permissions = RelationPermissionEnum::sanitize($permissions, AllianceRelationTypeEnum::ALLIED);
        if (($permissions & RelationPermissionEnum::SHARE_LIVE_MAP_POSITIONS->value) === 0) {
            return;
        }

        $relationPermission = $this
            ->prototype()
            ->setContact($contact)
            ->setPermission(RelationPermissionEnum::SHARE_LIVE_MAP_POSITIONS)
            ->setDirection(RelationPermissionDirectionEnum::SOURCE_TO_RECIPIENT);
        $contact->addRelationPermission($relationPermission);
        $this->save($relationPermission);
    }

    public function hasSamePermissions(Relation $relation, int $permissions): bool
    {
        $permissions = RelationPermissionEnum::sanitize($permissions, $relation->getType());
        foreach (RelationPermissionEnum::cases() as $permission) {
            if ($relation->hasPermission($permission) !== (($permissions & $permission->value) !== 0)) {
                return false;
            }
        }

        return true;
    }

    public function proposeForRelation(Relation $relation, int $permissions, bool $offeredBySource): bool
    {
        $permissions = RelationPermissionEnum::sanitize($permissions, $relation->getType());
        if ($this->hasSamePermissions($relation, $permissions)) {
            return false;
        }

        $this->deletePendingForRelation($relation);
        foreach (RelationPermissionEnum::cases() as $permission) {
            $granted = ($permissions & $permission->value) !== 0;
            if ($relation->hasPermission($permission) === $granted) {
                continue;
            }

            $relationPermission = $this
                ->prototype()
                ->setRelation($relation)
                ->setPermission($permission)
                ->setPending(true)
                ->setGranted($granted)
                ->setOfferedBySource($offeredBySource);
            $relation->addRelationPermission($relationPermission);
            $this->save($relationPermission);
        }

        return true;
    }

    public function acceptPendingForRelation(Relation $relation): bool
    {
        $pendingPermissions = array_filter(
            $relation->getRelationPermissions()->toArray(),
            static fn(RelationPermission $permission): bool => $permission->isPending()
        );
        if ($pendingPermissions === []) {
            return false;
        }

        foreach ($pendingPermissions as $pendingPermission) {
            $activePermission = $this->getActivePermission($relation, $pendingPermission->getPermission());
            if (!$pendingPermission->isGranted()) {
                if ($activePermission !== null) {
                    $relation->removeRelationPermission($activePermission);
                    $this->getEntityManager()->remove($activePermission);
                }
                $relation->removeRelationPermission($pendingPermission);
                $this->getEntityManager()->remove($pendingPermission);
                continue;
            }

            if ($activePermission === null) {
                $pendingPermission->setPending(false)->setOfferedBySource(false);
                continue;
            }

            $activePermission->setDirection($pendingPermission->getDirection());
            $relation->removeRelationPermission($pendingPermission);
            $this->getEntityManager()->remove($pendingPermission);
        }

        return true;
    }

    public function discardPendingForRelation(Relation $relation): bool
    {
        if (!$relation->hasPendingPermissionChanges()) {
            return false;
        }

        foreach ($relation->getRelationPermissions()->toArray() as $permission) {
            if (!$permission->isPending()) {
                continue;
            }

            $relation->removeRelationPermission($permission);
            $this->getEntityManager()->remove($permission);
        }

        return true;
    }

    public function deleteByRelation(Relation $relation): void
    {
        $this->deleteWhere('rp.relation = :relation', ['relation' => $relation]);
    }

    public function deleteByRelations(array $relations): void
    {
        if ($relations === []) {
            return;
        }

        $this->deleteWhere('rp.relation IN (:relations)', ['relations' => $relations]);
    }

    public function deleteByContact(Contact $contact): void
    {
        $this->deleteWhere('rp.contact = :contact', ['contact' => $contact]);
    }

    public function deleteByContacts(array $contacts): void
    {
        if ($contacts === []) {
            return;
        }

        $this->deleteWhere('rp.contact IN (:contacts)', ['contacts' => $contacts]);
    }

    private function deletePendingForRelation(Relation $relation): void
    {
        $this->deleteWhere('rp.relation = :relation AND rp.pending = true', ['relation' => $relation]);
    }

    private function getActivePermission(
        Relation $relation,
        RelationPermissionEnum $permission
    ): ?RelationPermission {
        foreach ($relation->getRelationPermissions() as $relationPermission) {
            if (!$relationPermission->isPending() && $relationPermission->getPermission() === $permission) {
                return $relationPermission;
            }
        }

        return null;
    }

    private function deleteWhere(string $where, array $parameters): void
    {
        $this
            ->getEntityManager()
            ->createQuery(
                sprintf('DELETE FROM %s rp WHERE %s', RelationPermission::class, $where)
            )
            ->setParameters($parameters)
            ->execute();
    }
}
