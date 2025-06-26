<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Creation;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\TholianWeb;
use Stu\StuTestCase;

class SpacecraftFactoryTest extends StuTestCase
{
    private MockInterface&SpacecraftRump $rump;

    private SpacecraftFactoryInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->rump = $this->mock(SpacecraftRump::class);

        $this->subject = new SpacecraftFactory();
    }

    public static function provideTestData(): array
    {
        return [
            [SpacecraftTypeEnum::SHIP, Ship::class],
            [SpacecraftTypeEnum::STATION, Station::class],
            [SpacecraftTypeEnum::THOLIAN_WEB, TholianWeb::class],
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

        $this->assertTrue($result->getCondition() instanceof SpacecraftCondition);
        $this->assertSame($result, $result->getCondition()->getSpacecraft());
    }
}
