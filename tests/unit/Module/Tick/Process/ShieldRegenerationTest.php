<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\System\Data\ShieldSystemData;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\MapFieldTypeInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\StuTestCase;

class ShieldRegenerationTest extends StuTestCase
{
    private MockInterface&SpacecraftRepositoryInterface $spacecraftRepository;
    private MockInterface&SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory;
    private MockInterface&StuTime $stuTime;

    private ProcessTickHandlerInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);
        $this->spacecraftWrapperFactory = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->stuTime = $this->mock(StuTime::class);

        $this->subject = new ShieldRegeneration(
            $this->spacecraftRepository,
            $this->spacecraftWrapperFactory,
            $this->stuTime
        );
    }

    public function testWorkExpectNothingIfTimerTooHigh(): void
    {
        $spacecraft = $this->mock(SpacecraftInterface::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $shieldSystemData = $this->mock(ShieldSystemData::class);

        $this->spacecraftRepository->shouldReceive('getSuitableForShieldRegeneration')
            ->withNoArgs()
            ->once()
            ->andReturn([$spacecraft]);

        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($spacecraft)
            ->once()
            ->andReturn($wrapper);

        $wrapper->shouldReceive('getShieldSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($shieldSystemData);

        $shieldSystemData->shouldReceive('getShieldRegenerationTimer')
            ->withNoArgs()
            ->once()
            ->andReturn(101);

        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(1000);

        $this->subject->work();
    }

    public function testWorkExpectNothingIfEffectIsPreventing(): void
    {
        $spacecraft = $this->mock(SpacecraftInterface::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $location = $this->mock(MapInterface::class);
        $fieldType = $this->mock(MapFieldTypeInterface::class);
        $shieldSystemData = $this->mock(ShieldSystemData::class);

        $this->spacecraftRepository->shouldReceive('getSuitableForShieldRegeneration')
            ->withNoArgs()
            ->once()
            ->andReturn([$spacecraft]);

        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($spacecraft)
            ->once()
            ->andReturn($wrapper);

        $spacecraft->shouldReceive('getLocation')
            ->withNoArgs()
            ->once()
            ->andReturn($location);

        $location->shouldReceive('getFieldType')
            ->withNoArgs()
            ->once()
            ->andReturn($fieldType);

        $fieldType->shouldReceive('hasEffect')
            ->with(FieldTypeEffectEnum::SHIELD_MALFUNCTION)
            ->once()
            ->andReturn(true);

        $wrapper->shouldReceive('getShieldSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($shieldSystemData);

        $shieldSystemData->shouldReceive('getShieldRegenerationTimer')
            ->withNoArgs()
            ->once()
            ->andReturn(100);

        $this->stuTime->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(1000);

        $this->subject->work();
    }
}
