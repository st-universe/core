<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Action\CreateRelation;

use Stu\Component\Alliance\Enum\RelationPermissionEnum;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class CreateRelationRequest implements CreateRelationRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getCounterpartId(): int
    {
        return $this->parameter('oid')->int()->required();
    }

    #[\Override]
    public function getRelationType(): int
    {
        return $this->parameter('type')->int()->required();
    }

    #[\Override]
    public function getPermissions(): int
    {
        $permissions = $this->parameter('relation_permissions')->int()->defaultsTo(0);
        foreach (RelationPermissionEnum::cases() as $permission) {
            if (
                $this->parameter('relation_permission_' . $permission->value)->int()->defaultsTo(0)
                === $permission->value
            ) {
                $permissions |= $permission->value;
            }
        }

        return $permissions;
    }
}
