<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Damage;

use Mockery\MockInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\Station;
use Stu\StuTestCase;

class SystemDamageTest extends StuTestCase
{
    private MockInterface&SpacecraftSystemManagerInterface $spacecraftSystemManager;

    private SystemDamage $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);

        $this->subject = new SystemDamage($this->spacecraftSystemManager);
    }

    public function testDamageShipSystemAddsDestructionInformationWhenSystemDestructionDestroysSpacecraft(): void
    {
        $station = new Station();
        $condition = new SpacecraftCondition($station);
        $station->setCondition($condition);

        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($station);

        $system = (new SpacecraftSystem())
            ->setSystemType(SpacecraftSystemTypeEnum::TORPEDO_STORAGE)
            ->setStatus(25)
            ->setMode(SpacecraftSystemModeEnum::MODE_ON);

        $this->spacecraftSystemManager->shouldReceive('handleDestroyedSystem')
            ->with($wrapper, SpacecraftSystemTypeEnum::TORPEDO_STORAGE)
            ->once()
            ->andReturnUsing(static function () use ($condition): void {
                $condition->setIsDestroyed(true);
            });

        $informations = new InformationWrapper();

        $result = $this->subject->damageShipSystem($wrapper, $system, 30, $informations);

        static::assertTrue($result);
        static::assertTrue($condition->isDestroyed());
        static::assertSame(0, $system->getStatus());
        static::assertSame(SpacecraftSystemModeEnum::MODE_OFF, $system->getMode());
        static::assertSame(
            [
                '- Der Schaden zerstört folgendes System: Torpedolager',
                '-- Das Schiff wurde zerstört!'
            ],
            $informations->getInformations()
        );
    }
}
