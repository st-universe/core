<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Mockery\MockInterface;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\System\Exception\SystemNotFoundException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class AlertLevelBasedReactionTest extends StuTestCase
{
    /** @var MockInterface|ShipSystemManagerInterface */
    private ShipSystemManagerInterface $shipSystemManager;

    /** @var MockInterface|ShipWrapperInterface */
    private ShipWrapperInterface $wrapper;

    /** @var MockInterface|ShipInterface */
    private ShipInterface $ship;

    private AlertLevelBasedReactionInterface $subject;

    public function setUp(): void
    {
        //injected
        $this->shipSystemManager = $this->mock(ShipSystemManagerInterface::class);

        //params
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        //other
        $this->ship = $this->mock(ShipInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new AlertLevelBasedReaction(
            $this->shipSystemManager
        );
    }

    public function testReactExpectChangeToYellowWhenGreen(): void
    {
        $this->ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(ShipAlertStateEnum::ALERT_GREEN);
        $this->wrapper->shouldReceive('setAlertState')
            ->with(ShipAlertStateEnum::ALERT_YELLOW)
            ->once()
            ->andReturn('alertMsg');

        $result = $this->subject->react($this->wrapper);

        $this->assertEquals(['- Erhöhung der Alarmstufe wurde durchgeführt, Grün -> Gelb', '- alertMsg'], $result->getInformations());
    }

    public function testReactExpectUncloakWhenYellowAndCloaked(): void
    {
        $this->ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->andReturn(ShipAlertStateEnum::ALERT_YELLOW);
        $this->ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->shipSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_CLOAK)
            ->once();

        $result = $this->subject->react($this->wrapper);

        $this->assertEquals(['- Die Tarnung wurde deaktiviert'], $result->getInformations());
    }

    public function testReactExpectShieldsNbsAndPhaserActivationWhenNotCloaked(): void
    {
        $this->ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->andReturn(ShipAlertStateEnum::ALERT_YELLOW);
        $this->ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->shipSystemManager->shouldReceive('activate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_SHIELDS)
            ->once();
        $this->shipSystemManager->shouldReceive('activate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_NBS)
            ->once();
        $this->shipSystemManager->shouldReceive('activate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_PHASER)
            ->once();


        $result = $this->subject->react($this->wrapper);

        $this->assertEquals([
            '- Die Schilde wurden aktiviert',
            '- Die Nahbereichssensoren wurden aktiviert',
            '- Die Energiewaffe wurde aktiviert'
        ], $result->getInformations());
    }

    public function testReactExpectNoShieldActivationWhenTractoring(): void
    {
        $this->ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->andReturn(ShipAlertStateEnum::ALERT_YELLOW);
        $this->ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->shipSystemManager->shouldReceive('activate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_NBS)
            ->once();
        $this->shipSystemManager->shouldReceive('activate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_PHASER)
            ->once();


        $result = $this->subject->react($this->wrapper);

        $this->assertEquals([
            '- Die Schilde konnten wegen aktiviertem Traktorstrahl nicht aktiviert werden',
            '- Die Nahbereichssensoren wurden aktiviert',
            '- Die Energiewaffe wurde aktiviert'
        ], $result->getInformations());
    }

    public function testReactExpectNoShieldActivationWhenTractored(): void
    {
        $this->ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->andReturn(ShipAlertStateEnum::ALERT_YELLOW);
        $this->ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->shipSystemManager->shouldReceive('activate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_NBS)
            ->once();
        $this->shipSystemManager->shouldReceive('activate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_PHASER)
            ->once();


        $result = $this->subject->react($this->wrapper);

        $this->assertEquals([
            '- Die Schilde konnten wegen aktiviertem Traktorstrahl nicht aktiviert werden',
            '- Die Nahbereichssensoren wurden aktiviert',
            '- Die Energiewaffe wurde aktiviert'
        ], $result->getInformations());
    }

    public function testReactExpectNothingWhenErrorsOnActivation(): void
    {
        $this->ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->andReturn(ShipAlertStateEnum::ALERT_YELLOW);
        $this->ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->shipSystemManager->shouldReceive('activate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_SHIELDS)
            ->once()
            ->andThrow(new SystemNotFoundException());
        $this->shipSystemManager->shouldReceive('activate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_NBS)
            ->once()
            ->andThrow(new SystemNotFoundException());
        $this->shipSystemManager->shouldReceive('activate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_PHASER)
            ->once()
            ->andThrow(new SystemNotFoundException());


        $result = $this->subject->react($this->wrapper);

        $this->assertEquals([], $result->getInformations());
    }

    public function testReactExpectShieldsNbsPhaserAndTorpedoActivation(): void
    {
        $this->ship->shouldReceive('getAlertState')
            ->withNoArgs()
            ->andReturn(ShipAlertStateEnum::ALERT_RED);
        $this->ship->shouldReceive('getCloakState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractored')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->shipSystemManager->shouldReceive('activate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_SHIELDS)
            ->once();
        $this->shipSystemManager->shouldReceive('activate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_NBS)
            ->once();
        $this->shipSystemManager->shouldReceive('activate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_PHASER)
            ->once();
        $this->shipSystemManager->shouldReceive('activate')
            ->with($this->wrapper, ShipSystemTypeEnum::SYSTEM_TORPEDO)
            ->once();

        $result = $this->subject->react($this->wrapper);

        $this->assertEquals([
            '- Die Schilde wurden aktiviert',
            '- Die Nahbereichssensoren wurden aktiviert',
            '- Die Energiewaffe wurde aktiviert',
            '- Der Torpedowerfer wurde aktiviert'
        ], $result->getInformations());
    }
}
