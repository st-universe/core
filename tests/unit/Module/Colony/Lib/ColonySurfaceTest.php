<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\BuildingRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\PlanetGenerator\PlanetGeneratorInterface;
use Stu\StuTestCase;

final class ColonySurfaceTest extends StuTestCase
{
    public function testSurfaceResetClearsRotationsOfExistingAndNewFields(): void
    {
        $existingField = new PlanetField()
            ->setFieldId(1)
            ->setBuilding(new Building())
            ->setRotation(270);
        $fields = new ArrayCollection([1 => $existingField]);
        $newField = new PlanetField();
        $host = $this->mock(Colony::class);
        $host->shouldReceive('isFree')->once()->andReturnTrue();
        $host->shouldReceive('getMask')->once()->andReturn(base64_encode(serialize([1 => 101, 2 => 102])));
        $host->shouldReceive('getPlanetFields')->andReturn($fields);
        $fieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $fieldRepository->shouldReceive('prototype')->once()->andReturn($newField);
        $fieldRepository->shouldReceive('save')->with($existingField)->once();
        $fieldRepository->shouldReceive('save')->with($newField)->once();
        $entityManager = $this->mock(EntityManagerInterface::class);
        $entityManager->shouldReceive('flush')->once();

        $subject = new ColonySurface(
            $fieldRepository,
            $this->mock(BuildingRepositoryInterface::class),
            $this->mock(ColonyRepositoryInterface::class),
            $this->mock(ResearchedRepositoryInterface::class),
            $this->mock(PlanetGeneratorInterface::class),
            $entityManager,
            $this->mock(PlanetFieldTypeRetrieverInterface::class),
            $host,
            null,
            true
        );

        $subject->updateSurface();

        self::assertSame(0, $existingField->getRotation());
        self::assertNull($existingField->getBuilding());
        self::assertSame(0, $newField->getRotation());
        self::assertSame($newField, $fields->get(2));
        self::assertSame(102, $newField->getFieldType());
    }
}
