<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Component\Alliance\Enum\RelationPermissionEnum;
use Stu\StuTestCase;

class RelationTest extends StuTestCase
{
    public function testPendingPermissionChangeDoesNotChangeActivePermission(): void
    {
        $relation = new Relation();
        $relation->addRelationPermission(
            new RelationPermission()
                ->setPermission(RelationPermissionEnum::FRIENDLY)
                ->setGranted(true)
        );
        $relation->addRelationPermission(
            new RelationPermission()
                ->setPermission(RelationPermissionEnum::FRIENDLY)
                ->setPending(true)
                ->setGranted(false)
        );

        $this->assertTrue($relation->hasPermission(RelationPermissionEnum::FRIENDLY));
        $this->assertTrue($relation->hasPendingPermissionChanges());
    }
}
