<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle;

use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\Exception\SystemNotFoundException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\StuTestCase;

class AlertLevelBasedReactionTest extends StuTestCase
{
    private MockInterface&SpacecraftSystemManagerInterface $spacecraftSystemManager;

    private MockInterface&ShipWrapperInterface $wrapper;

    private MockInterface&InformationInterface $informations;

    private MockInterface&Ship $ship;

    private AlertLevelBasedReactionInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);

        //params
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->informations = $this->mock(InformationInterface::class);

        //other
        $this->ship = $this->mock(Ship::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->subject = new AlertLevelBasedReaction(
            $this->spacecraftSystemManager
        );
    }

    public function testReactExpectChangeToYellowWhenGreen(): void
    {
        $this->wrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_GREEN);
        $this->wrapper->shouldReceive('setAlertState')
            ->with(SpacecraftAlertStateEnum::ALERT_YELLOW)
            ->once()
            ->andReturn('alertMsg');

        $this->informations->shouldReceive('addInformation')
            ->with('- Erhöhung der Alarmstufe wurde durchgeführt, Grün -> Gelb')
            ->once();
        $this->informations->shouldReceive('addInformation')
            ->with('- alertMsg')
            ->once();

        $this->subject->react($this->wrapper, $this->informations);
    }

    public function testReactExpectUncloakWhenYellowAndCloaked(): void
    {
        $this->wrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_YELLOW);
        $this->ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->spacecraftSystemManager->shouldReceive('deactivate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::CLOAK)
            ->once();

        $this->informations->shouldReceive('addInformation')
            ->with('- Die Tarnung wurde deaktiviert')
            ->once();

        $this->subject->react($this->wrapper, $this->informations);
    }

    public function testReactExpectShieldsNbsAndPhaserActivationWhenNotCloaked(): void
    {
        $this->wrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_YELLOW);
        $this->ship->shouldReceive('isCloaked')
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

        $this->spacecraftSystemManager->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::SHIELDS)
            ->once();
        $this->spacecraftSystemManager->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::NBS)
            ->once();
        $this->spacecraftSystemManager->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::PHASER)
            ->once();

        $this->informations->shouldReceive('addInformation')
            ->with('- Die Schilde wurden aktiviert')
            ->once();
        $this->informations->shouldReceive('addInformation')
            ->with('- Die Nahbereichssensoren wurden aktiviert')
            ->once();
        $this->informations->shouldReceive('addInformation')
            ->with('- Die Energiewaffe wurde aktiviert')
            ->once();

        $this->subject->react($this->wrapper, $this->informations);
    }

    public function testReactExpectNoShieldActivationWhenTractoring(): void
    {
        $this->wrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_YELLOW);
        $this->ship->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('isTractoring')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->spacecraftSystemManager->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::NBS)
            ->once();
        $this->spacecraftSystemManager->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::PHASER)
            ->once();

        $this->informations->shouldReceive('addInformation')
            ->with('- Die Schilde konnten wegen aktiviertem Traktorstrahl nicht aktiviert werden')
            ->once();
        $this->informations->shouldReceive('addInformation')
            ->with('- Die Nahbereichssensoren wurden aktiviert')
            ->once();
        $this->informations->shouldReceive('addInformation')
            ->with('- Die Energiewaffe wurde aktiviert')
            ->once();

        $this->subject->react($this->wrapper, $this->informations);
    }

    public function testReactExpectNoShieldActivationWhenTractored(): void
    {
        $this->wrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_YELLOW);
        $this->ship->shouldReceive('isCloaked')
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

        $this->spacecraftSystemManager->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::NBS)
            ->once();
        $this->spacecraftSystemManager->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::PHASER)
            ->once();

        $this->informations->shouldReceive('addInformation')
            ->with('- Die Schilde konnten wegen aktiviertem Traktorstrahl nicht aktiviert werden')
            ->once();
        $this->informations->shouldReceive('addInformation')
            ->with('- Die Nahbereichssensoren wurden aktiviert')
            ->once();
        $this->informations->shouldReceive('addInformation')
            ->with('- Die Energiewaffe wurde aktiviert')
            ->once();

        $this->subject->react($this->wrapper, $this->informations);
    }

    public function testReactExpectNothingWhenErrorsOnActivation(): void
    {
        $this->wrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_YELLOW);
        $this->ship->shouldReceive('isCloaked')
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

        $this->spacecraftSystemManager->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::SHIELDS)
            ->once()
            ->andThrow(new SystemNotFoundException());
        $this->spacecraftSystemManager->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::NBS)
            ->once()
            ->andThrow(new SystemNotFoundException());
        $this->spacecraftSystemManager->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::PHASER)
            ->once()
            ->andThrow(new SystemNotFoundException());

        $this->subject->react($this->wrapper, $this->informations);
    }

    public function testReactExpectShieldsNbsPhaserAndTorpedoActivation(): void
    {
        $this->wrapper->shouldReceive('getAlertState')
            ->withNoArgs()
            ->andReturn(SpacecraftAlertStateEnum::ALERT_RED);
        $this->ship->shouldReceive('isCloaked')
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

        $this->spacecraftSystemManager->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::SHIELDS)
            ->once();
        $this->spacecraftSystemManager->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::NBS)
            ->once();
        $this->spacecraftSystemManager->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::PHASER)
            ->once();
        $this->spacecraftSystemManager->shouldReceive('activate')
            ->with($this->wrapper, SpacecraftSystemTypeEnum::TORPEDO)
            ->once();

        $this->informations->shouldReceive('addInformation')
            ->with('- Die Schilde wurden aktiviert')
            ->once();
        $this->informations->shouldReceive('addInformation')
            ->with('- Die Nahbereichssensoren wurden aktiviert')
            ->once();
        $this->informations->shouldReceive('addInformation')
            ->with('- Die Energiewaffe wurde aktiviert')
            ->once();
        $this->informations->shouldReceive('addInformation')
            ->with('- Der Torpedowerfer wurde aktiviert')
            ->once();

        $this->subject->react($this->wrapper, $this->informations);
    }
}
