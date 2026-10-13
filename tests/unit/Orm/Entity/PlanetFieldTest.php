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
