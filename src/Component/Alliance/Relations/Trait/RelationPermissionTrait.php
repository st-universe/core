<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Relations\Trait;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Alliance\Enum\RelationPermissionEnum;
use Stu\Orm\Entity\RelationPermission;
use Stu\Orm\Entity\User;

trait RelationPermissionTrait
{
    /** @return Collection<int, RelationPermission> */
    public function getRelationPermissions(): Collection
    {
        return $this->relationPermissions;
    }

    public function addRelationPermission(RelationPermission $permission): self
    {
        if (!$this->relationPermissions->contains($permission)) {
            $this->relationPermissions->add($permission);
        }

        return $this;
    }

    public function removeRelationPermission(RelationPermission $permission): self
    {
        $this->relationPermissions->removeElement($permission);
        return $this;
    }

    public function hasPermissions(): bool
    {
        foreach ($this->relationPermissions as $relationPermission) {
            if (!$relationPermission->isPending() && $relationPermission->isGranted()) {
                return true;
            }
        }

        return false;
    }

    public function hasPendingPermissionChanges(): bool
    {
        foreach ($this->relationPermissions as $relationPermission) {
            if ($relationPermission->isPending()) {
                return true;
            }
        }

        return false;
    }

    public function isPermissionChangeOfferedBy(User $user): bool
    {
        foreach ($this->relationPermissions as $relationPermission) {
            if (!$relationPermission->isPending()) {
                continue;
            }

            return $relationPermission->isOfferedBySource()
                ? $this->isSourceParty($user)
                : $this->isRecipientParty($user);
        }

        return false;
    }

    public function hasPermission(RelationPermissionEnum $permission): bool
    {
        foreach ($this->relationPermissions as $relationPermission) {
            if (
                !$relationPermission->isPending()
                && $relationPermission->isGranted()
                && $relationPermission->getPermission() === $permission
            ) {
                return true;
            }
        }

        return false;
    }

    public function hasPermissionFor(User $user, RelationPermissionEnum $permission): bool
    {
        foreach ($this->relationPermissions as $relationPermission) {
            if (
                $relationPermission->isGranted()
                && !$relationPermission->isPending()
                && $relationPermission->getPermission() === $permission
                && $relationPermission->isGrantedTo($user)
            ) {
                return true;
            }
        }

        return false;
    }
}
