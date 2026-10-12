<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use InvalidArgumentException;
use PHPUnit\Framework\Attributes\TestWith;
use Stu\StuTestCase;

final class PlanetFieldTest extends StuTestCase
{
    public function testNewFieldHasNoRotation(): void
    {
        self::assertSame(0, new PlanetField()->getRotation());
    }

    #[TestWith([0, 0, '42/0a'])]
    #[TestWith([90, 1, '42/90a'])]
    #[TestWith([180, 0, '42/180a'])]
    #[TestWith([270, 100, '42/270b'])]
    public function testPictureIncludesRotationAndBuildingState(int $rotation, int $active, string $picture): void
    {
        $building = $this->mock(Building::class);
        $building->shouldReceive('getId')->andReturn(42);
        $field = new PlanetField()
            ->setBuilding($building)
            ->setRotation($rotation)
            ->setActive($active);

        self::assertSame($picture, $field->getPictureType());
    }

    #[TestWith([-90])]
    #[TestWith([45])]
    #[TestWith([360])]
    public function testInvalidRotationIsRejected(int $rotation): void
    {
        $this->expectException(InvalidArgumentException::class);

        new PlanetField()->setRotation($rotation);
    }

    public function testDemolitionClearsRotation(): void
    {
        $field = new PlanetField()
            ->setBuilding(new Building())
            ->setRotation(270)
            ->setIntegrity(100)
            ->setActive(1);

        $field->clearBuilding();

        self::assertNull($field->getBuilding());
        self::assertSame(0, $field->getRotation());
        self::assertSame(0, $field->getIntegrity());
        self::assertSame(0, $field->getActive());
    }
}
