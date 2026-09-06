<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mockery;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\Alliance\Enum\RelationPermissionEnum;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Entity\RelationPermission;
use Stu\StuTestCase;

class RelationPermissionRepositoryTest extends StuTestCase
{
    public function testReplaceForNewRelationDoesNotRequireAnIdentifier(): void
    {
        $entityManager = $this->mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('persist')->with(Mockery::type(RelationPermission::class))->once();

        $repository = new RelationPermissionRepository(
            $entityManager,
            new ClassMetadata(RelationPermission::class)
        );
        $relation = new Relation()->setType(AllianceRelationTypeEnum::ALLIED);

        $repository->replaceForRelation(
            $relation,
            RelationPermissionEnum::FRIENDLY->value,
            AllianceRelationTypeEnum::ALLIED
        );

        $this->assertTrue($relation->hasPermission(RelationPermissionEnum::FRIENDLY));
    }

    public function testAcceptPendingPermissionChangeKeepsTheOfferedPermission(): void
    {
        $entityManager = $this->mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('remove')->with(Mockery::type(RelationPermission::class))->twice();

        $repository = new RelationPermissionRepository(
            $entityManager,
            new ClassMetadata(RelationPermission::class)
        );
        $relation = new Relation()->setType(AllianceRelationTypeEnum::ALLIED);
        $relation->addRelationPermission(
            new RelationPermission()
                ->setPermission(RelationPermissionEnum::FRIENDLY)
                ->setRelation($relation)
        );
        $relation->addRelationPermission(
            new RelationPermission()
                ->setPermission(RelationPermissionEnum::FRIENDLY)
                ->setPending(true)
                ->setGranted(false)
                ->setRelation($relation)
        );
        $relation->addRelationPermission(
            new RelationPermission()
                ->setPermission(RelationPermissionEnum::SHARE_LIVE_MAP_POSITIONS)
                ->setPending(true)
                ->setGranted(true)
                ->setRelation($relation)
        );

        $this->assertTrue($repository->acceptPendingForRelation($relation));
        $this->assertFalse($relation->hasPermission(RelationPermissionEnum::FRIENDLY));
        $this->assertTrue($relation->hasPermission(RelationPermissionEnum::SHARE_LIVE_MAP_POSITIONS));
        $this->assertFalse($relation->hasPendingPermissionChanges());
    }
}
