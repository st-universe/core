<?php

declare(strict_types=1);

namespace Stu\Component\Map\Effects\Type;

use Mockery\MockInterface;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\StuTestCase;

class RegenerationChanceEffectHandlerTest extends StuTestCase
{
    private MockInterface&StuRandom $stuRandom;

    private RegenerationChanceEffectHandler $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->stuRandom = $this->mock(StuRandom::class);

        $this->subject = new RegenerationChanceEffectHandler($this->stuRandom);
    }

    public function testHandleIncomingSpacecraftCapsShieldGainAtEffectiveMaximum(): void
    {
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $spacecraft = $this->mock(Spacecraft::class);
        $condition = $this->mock(SpacecraftCondition::class);
        $information = $this->mock(MessageCollectionInterface::class);

        $this->expectShieldRegenerationRolls(20, 10);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->twice()
            ->andReturn($spacecraft);

        $spacecraft->shouldReceive('isSystemHealthy')
            ->with(SpacecraftSystemTypeEnum::SHIELDS)
            ->once()
            ->andReturnTrue();
        $spacecraft->shouldReceive('getMaxShield')
            ->withNoArgs()
            ->once()
            ->andReturn(50);
        $spacecraft->shouldReceive('getCondition')
            ->withNoArgs()
            ->once()
            ->andReturn($condition);
        $spacecraft->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('Testschiff');

        $condition->shouldReceive('getShield')
            ->withNoArgs()
            ->once()
            ->andReturn(45);
        $condition->shouldReceive('changeShield')
            ->with(5)
            ->once();

        $information->shouldReceive('addInformationf')
            ->with(
                '%s: [color=green]%s wird um %s aufgeladen[/color]',
                'Testschiff',
                'Schilde',
                5
            )
            ->once()
            ->andReturnSelf();

        $this->subject->handleIncomingSpacecraft($wrapper, $information);
    }

    public function testHandleIncomingSpacecraftDoesNotChangeShieldIfAlreadyAtEffectiveMaximum(): void
    {
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $spacecraft = $this->mock(Spacecraft::class);
        $condition = $this->mock(SpacecraftCondition::class);
        $information = $this->mock(MessageCollectionInterface::class);

        $this->expectShieldRegenerationRolls();

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($spacecraft);

        $spacecraft->shouldReceive('isSystemHealthy')
            ->with(SpacecraftSystemTypeEnum::SHIELDS)
            ->once()
            ->andReturnTrue();
        $spacecraft->shouldReceive('getMaxShield')
            ->withNoArgs()
            ->once()
            ->andReturn(50);
        $spacecraft->shouldReceive('getCondition')
            ->withNoArgs()
            ->once()
            ->andReturn($condition);

        $condition->shouldReceive('getShield')
            ->withNoArgs()
            ->once()
            ->andReturn(60);
        $condition->shouldReceive('changeShield')
            ->never();

        $information->shouldReceive('addInformationf')
            ->never();

        $this->subject->handleIncomingSpacecraft($wrapper, $information);
    }

    private function expectShieldRegenerationRolls(?int $shieldGain = null, ?int $shieldGainCap = null): void
    {
        $this->stuRandom->shouldReceive('rand')
            ->with(1, 100)
            ->once()
            ->andReturn(1);
        $this->stuRandom->shouldReceive('array_rand')
            ->with([
                SpacecraftModuleTypeEnum::EPS,
                SpacecraftModuleTypeEnum::REACTOR,
                SpacecraftModuleTypeEnum::SHIELDS,
                SpacecraftModuleTypeEnum::WARPDRIVE
            ])
            ->once()
            ->andReturn(2);
        $this->stuRandom->shouldReceive('rand')
            ->with(1, 20, true, 5)
            ->once()
            ->andReturn(20);

        if ($shieldGain !== null && $shieldGainCap !== null) {
            $this->stuRandom->shouldReceive('rand')
                ->with(1, $shieldGainCap, true, $shieldGainCap * 2)
                ->once()
                ->andReturn($shieldGain);
        }
    }
}
