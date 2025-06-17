<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Creation;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftConditionInterface;
use Stu\Orm\Entity\SpacecraftRumpInterface;
use Stu\Orm\Entity\StationInterface;
use Stu\Orm\Entity\TholianWebInterface;
use Stu\StuTestCase;

class SpacecraftFactoryTest extends StuTestCase
{
    private MockInterface&SpacecraftRumpInterface $rump;

    private SpacecraftFactoryInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->rump = $this->mock(SpacecraftRumpInterface::class);

        $this->subject = new SpacecraftFactory();
    }

    public static function provideTestData(): array
    {
        return [
            [SpacecraftTypeEnum::SHIP, ShipInterface::class],
            [SpacecraftTypeEnum::STATION, StationInterface::class],
            [SpacecraftTypeEnum::THOLIAN_WEB, TholianWebInterface::class],
        ];
    }

    /** @param class-string $className */
    #[DataProvider('provideTestData')]
    public function testCreate(
        SpacecraftTypeEnum $type,
        string $className
    ): void {

        $this->rump->shouldReceive('getShipRumpCategory->getType')
            ->withNoArgs()
            ->once()
            ->andReturn($type);

        $result = $this->subject->create($this->rump);

        $this->assertTrue($result instanceof $className);

        $this->assertTrue($result->getCondition() instanceof SpacecraftConditionInterface);
        $this->assertSame($result, $result->getCondition()->getSpacecraft());
    }
}
