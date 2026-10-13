<?php

declare(strict_types=1);

namespace Stu\Lib\Colony;

use PHPUnit\Framework\Attributes\TestWith;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\PlanetField;
use Stu\StuTestCase;

final class PlanetFieldImageTest extends StuTestCase
{
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

        self::assertSame($picture, PlanetFieldImage::getPictureType($field));
    }

    public function testPictureWithoutBuildingUsesUndefinedRotation(): void
    {
        $field = new PlanetField()->setRotation(90);

        self::assertSame('/UNDEFINEDa', PlanetFieldImage::getPictureType($field));
    }
}
