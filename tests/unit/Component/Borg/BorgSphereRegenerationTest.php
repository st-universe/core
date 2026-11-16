<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\StuTestCase;

final class BorgSphereRegenerationTest extends StuTestCase
{
    private StuRandom&MockInterface $random;
    private BorgSphereRegeneration $subject;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->random = $this->mock(StuRandom::class);
        $this->subject = new BorgSphereRegeneration($this->random);
    }

    public function testDoesNotRegenerateBeforeFirstDisable(): void
    {
        $ship = $this->ship();
        $event = $this->event()->setFirstDisabledAt(null);
        $this->random->shouldNotReceive('rand');
        $this->subject->regenerate($event, $ship, 10);
        self::assertSame(100, $ship->getCondition()->getHull());
        self::assertSame(1000, $event->getTranswarpProgress());
    }

    public function testHourlyRegenerationRollsEachValueIndependentlyAndPreservesTranswarpFloors(): void
    {
        $ship = $this->ship();
        $event = $this->event()->setTranswarpProgress(1900);
        $this->random->shouldReceive('rand')->with(2, 10)->times(5)->andReturn(2, 3, 4, 5, 6);
        $this->subject->regenerate($event, $ship, 1);
        self::assertSame(120, $ship->getCondition()->getHull());
        self::assertSame(260, $ship->getCondition()->getShield());
        self::assertSame(14, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus());
        self::assertSame(15, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE)->getStatus());
        self::assertSame(16, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->getStatus());
        self::assertSame(2000, $event->getTranswarpProgress());
        self::assertSame(2000, $event->getTranswarpFloor());
        self::assertFalse($ship->getCondition()->isDisabled());
    }

    public function testCubeShieldsRegenerateMoreSlowlyWithoutSlowingHullOrSystems(): void
    {
        $ship = $this->ship();
        $event = $this->event();
        $event->getConfiguration()->setSettings([
            ...$event->getConfiguration()->getSettings(),
            'assimilation_enabled' => 1,
            'shield_regen_min_percent' => 1,
            'shield_regen_max_percent' => 5
        ]);
        $this->random->shouldReceive('rand')->with(2, 10)->times(4)->andReturn(10);
        $this->random->shouldReceive('rand')->with(1, 5)->once()->andReturn(1);

        $this->subject->regenerate($event, $ship, 1);

        self::assertSame(200, $ship->getCondition()->getHull());
        self::assertSame(220, $ship->getCondition()->getShield());
        self::assertSame(20, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus());
        self::assertSame(20, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE)->getStatus());
        self::assertSame(11, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRANSWARP_COIL)->getStatus());
    }

    public function testHalfHourlyTicksRoundEachSystemUpIndependentlyAndPreserveTranswarpProgress(): void
    {
        $ship = $this->ship();
        $event = $this->event();
        $event->getConfiguration()->setSettings([...$event->getConfiguration()->getSettings(), 'tick_seconds' => 1800]);
        $this->random->shouldReceive('rand')->with(2, 10)->times(10)->andReturn(2, 3, 5, 7, 9, 2, 3, 5, 7, 9);

        $this->subject->regenerate($event, $ship, 1);

        self::assertSame(110, $ship->getCondition()->getHull());
        self::assertSame(230, $ship->getCondition()->getShield());
        self::assertSame(13, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus());
        self::assertSame(14, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE)->getStatus());
        self::assertSame(15, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->getStatus());
        self::assertSame(1050, $event->getTranswarpProgress());
        self::assertSame(1000, $event->getTranswarpFloor());
        self::assertSame(10, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRANSWARP_COIL)->getStatus());

        $this->subject->regenerate($event, $ship, 1);

        self::assertSame(120, $ship->getCondition()->getHull());
        self::assertSame(260, $ship->getCondition()->getShield());
        self::assertSame(16, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus());
        self::assertSame(18, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE)->getStatus());
        self::assertSame(20, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->getStatus());
        self::assertSame(1100, $event->getTranswarpProgress());
        self::assertSame(11, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRANSWARP_COIL)->getStatus());
    }

    public function testHalfHourlyCubeShieldRatesAreHalvedSeparatelyFromHullAndSystems(): void
    {
        $ship = $this->ship();
        $event = $this->event();
        $event->getConfiguration()->setSettings([
            ...$event->getConfiguration()->getSettings(),
            'tick_seconds' => 1800,
            'assimilation_enabled' => 1,
            'shield_regen_min_percent' => 1,
            'shield_regen_max_percent' => 5
        ]);
        $this->random->shouldReceive('rand')->with(2, 10)->times(8)->andReturn(10);
        $this->random->shouldReceive('rand')->with(1, 5)->twice()->andReturn(1, 5);

        $this->subject->regenerate($event, $ship, 1);

        self::assertSame(150, $ship->getCondition()->getHull());
        self::assertSame(210, $ship->getCondition()->getShield());
        self::assertSame(15, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus());
        self::assertSame(1050, $event->getTranswarpProgress());

        $this->subject->regenerate($event, $ship, 1);

        self::assertSame(200, $ship->getCondition()->getHull());
        self::assertSame(260, $ship->getCondition()->getShield());
        self::assertSame(20, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus());
        self::assertSame(1100, $event->getTranswarpProgress());
    }

    public function testHourlyTicksPreserveThePreviousCeilingForIndivisibleMaximumValues(): void
    {
        $ship = $this->ship()->setMaxHull(1001)->setMaxShield(2003);
        $event = $this->event();
        $this->random->shouldReceive('rand')->with(2, 10)->times(5)->andReturn(3);

        $this->subject->regenerate($event, $ship, 1);

        self::assertSame(131, $ship->getCondition()->getHull());
        self::assertSame(261, $ship->getCondition()->getShield());
        self::assertSame(13, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus());
    }

    public function testHalfHourlyTicksRoundHullShieldAndSystemFractionsUp(): void
    {
        $ship = $this->ship()->setMaxHull(1001)->setMaxShield(2003);
        $event = $this->event();
        $event->getConfiguration()->setSettings([...$event->getConfiguration()->getSettings(), 'tick_seconds' => 1800]);
        $this->random->shouldReceive('rand')->with(2, 10)->times(5)->andReturn(3);

        $this->subject->regenerate($event, $ship, 1);

        self::assertSame(116, $ship->getCondition()->getHull());
        self::assertSame(231, $ship->getCondition()->getShield());
        self::assertSame(12, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus());
        self::assertSame(1050, $event->getTranswarpProgress());
    }

    public function testOverdueHalfHourlyTicksRollAndRoundUpIndependently(): void
    {
        $ship = $this->ship();
        $event = $this->event();
        $event->getConfiguration()->setSettings([...$event->getConfiguration()->getSettings(), 'tick_seconds' => 1800]);
        $this->random->shouldReceive('rand')->with(2, 10)->times(10)->andReturn(2, 3, 5, 7, 9, 2, 3, 2, 3, 4);

        $this->subject->regenerate($event, $ship, 2);

        self::assertSame(120, $ship->getCondition()->getHull());
        self::assertSame(260, $ship->getCondition()->getShield());
        self::assertSame(14, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus());
        self::assertSame(16, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE)->getStatus());
        self::assertSame(17, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->getStatus());
        self::assertSame(1100, $event->getTranswarpProgress());
    }

    public function testHalvedTranswarpRateKeepsAdaptationAndEscapeCaps(): void
    {
        $ship = $this->ship();
        $event = $this->event()->setTranswarpProgress(1975);
        $event->getConfiguration()->setSettings([...$event->getConfiguration()->getSettings(), 'tick_seconds' => 1800]);
        $this->random->shouldReceive('rand')->with(2, 10)->times(505)->andReturn(10);

        $this->subject->regenerate($event, $ship, 1);

        self::assertSame(2025, $event->getTranswarpProgress());
        self::assertSame(2000, $event->getTranswarpFloor());
        self::assertSame(20, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRANSWARP_COIL)->getStatus());
        $event->setTranswarpProgress(7975);

        $this->subject->regenerate($event, $ship, 100);

        self::assertSame(1000, $ship->getCondition()->getHull());
        self::assertSame(2000, $ship->getCondition()->getShield());
        self::assertSame(100, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus());
        self::assertSame(8000, $event->getTranswarpProgress());
        self::assertSame(7000, $event->getTranswarpFloor());
    }

    public function testFractionalTranswarpBasisPointsRoundUpForEveryTick(): void
    {
        $ship = $this->ship();
        $event = $this->event();
        $event->getConfiguration()->setSettings([...$event->getConfiguration()->getSettings(), 'tick_seconds' => 1800, 'transwarp_regen_bp' => 101]);
        $this->random->shouldReceive('rand')->with(2, 10)->times(10)->andReturn(2);

        $this->subject->regenerate($event, $ship, 1);

        self::assertSame(1051, $event->getTranswarpProgress());

        $this->subject->regenerate($event, $ship, 1);

        self::assertSame(1102, $event->getTranswarpProgress());
    }

    public function testOverdueTicksRoundEachTranswarpIncreaseBeforeCombiningThem(): void
    {
        $ship = $this->ship();
        $event = $this->event();
        $event->getConfiguration()->setSettings([...$event->getConfiguration()->getSettings(), 'tick_seconds' => 1800, 'transwarp_regen_bp' => 101]);
        $this->random->shouldReceive('rand')->with(2, 10)->times(10)->andReturn(2);

        $this->subject->regenerate($event, $ship, 2);

        self::assertSame(1102, $event->getTranswarpProgress());
    }

    public function testOverdueHoursRollEachValueAgainRatherThanMultiplyingOneRoll(): void
    {
        $ship = $this->ship();
        $event = $this->event();
        $this->random->shouldReceive('rand')->with(2, 10)->times(10)->andReturn(2, 3, 4, 5, 6, 7, 8, 9, 10, 2);
        $this->subject->regenerate($event, $ship, 2);
        self::assertSame(190, $ship->getCondition()->getHull());
        self::assertSame(420, $ship->getCondition()->getShield());
        self::assertSame(23, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus());
        self::assertSame(25, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE)->getStatus());
        self::assertSame(18, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->getStatus());
        self::assertSame(1200, $event->getTranswarpProgress());
        self::assertSame(12, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRANSWARP_COIL)->getStatus());
    }

    public function testTheFirstHourWakesSensorsAndWeaponsButKeepsWarpAndTractorOff(): void
    {
        $ship = $this->ship();
        foreach ([SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::PHASER, SpacecraftSystemTypeEnum::TORPEDO, SpacecraftSystemTypeEnum::IMPULSEDRIVE] as $type) {
            $ship->getSystems()->set($type->value, new SpacecraftSystem()->setSpacecraft($ship)->setSystemType($type)->setStatus(10)->setMode(SpacecraftSystemModeEnum::MODE_OFF));
        }
        $event = $this->event();
        $this->random->shouldReceive('rand')->with(2, 10)->times(9)->andReturn(2);
        $this->subject->regenerate($event, $ship, 1);
        self::assertFalse($ship->getCondition()->isDisabled());
        self::assertSame(BorgSpherePhaseEnum::ROAMING, $event->getPhase());
        self::assertSame(SpacecraftSystemModeEnum::MODE_ON, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getMode());
        self::assertTrue($ship->hasActiveWeapon());
        self::assertSame(SpacecraftSystemModeEnum::MODE_ON, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::NBS)->getMode());
        self::assertSame(SpacecraftSystemModeEnum::MODE_ON, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TORPEDO)->getMode());
        self::assertSame(SpacecraftSystemModeEnum::MODE_ON, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::IMPULSEDRIVE)->getMode());
        self::assertSame(SpacecraftSystemModeEnum::MODE_OFF, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE)->getMode());
        self::assertSame(SpacecraftSystemModeEnum::MODE_OFF, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->getMode());
    }

    /** @return iterable<string, array{BorgSpherePhaseEnum, bool}> */
    public static function repairedShipPhases(): iterable
    {
        yield 'first activation after regeneration' => [BorgSpherePhaseEnum::REGENERATING, true];
        yield 'repair after the ship already resumed roaming' => [BorgSpherePhaseEnum::ROAMING, false];
    }

    #[DataProvider('repairedShipPhases')]
    public function testFullyDestroyedSensorsAndWeaponsReactivateWhenRepaired(BorgSpherePhaseEnum $phase, bool $disabled): void
    {
        $ship = $this->ship();
        $ship->getCondition()->setDisabled($disabled);
        foreach ([SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::PHASER, SpacecraftSystemTypeEnum::TORPEDO, SpacecraftSystemTypeEnum::CLOAK] as $type) {
            $ship->getSystems()->set($type->value, new SpacecraftSystem()->setSpacecraft($ship)->setSystemType($type)->setStatus(0)->setMode(SpacecraftSystemModeEnum::MODE_OFF));
        }
        $event = $this->event()->setPhase($phase);
        $this->random->shouldReceive('rand')->with(2, 10)->andReturn(2);

        $this->subject->regenerate($event, $ship, 1);

        self::assertFalse($ship->getCondition()->isDisabled());
        self::assertSame(BorgSpherePhaseEnum::ROAMING, $event->getPhase());
        foreach ([SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::PHASER, SpacecraftSystemTypeEnum::TORPEDO] as $type) {
            self::assertSame(2, $ship->getSpacecraftSystem($type)->getStatus());
            self::assertSame(SpacecraftSystemModeEnum::MODE_ON, $ship->getSpacecraftSystem($type)->getMode());
        }
        foreach ([SpacecraftSystemTypeEnum::WARPDRIVE, SpacecraftSystemTypeEnum::CLOAK, SpacecraftSystemTypeEnum::TRACTOR_BEAM, SpacecraftSystemTypeEnum::TRANSWARP_COIL] as $type) {
            self::assertSame(SpacecraftSystemModeEnum::MODE_OFF, $ship->getSpacecraftSystem($type)->getMode());
        }
    }

    public function testSystemsStillAtZeroPercentRemainOffOnAnActiveShip(): void
    {
        $ship = $this->ship();
        $ship->getCondition()->setDisabled(false);
        foreach ([SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::PHASER, SpacecraftSystemTypeEnum::TORPEDO] as $type) {
            $ship->getSystems()->set($type->value, new SpacecraftSystem()->setSpacecraft($ship)->setSystemType($type)->setStatus(0)->setMode(SpacecraftSystemModeEnum::MODE_OFF));
        }
        $event = $this->event()->setPhase(BorgSpherePhaseEnum::ROAMING);
        $event->getConfiguration()->setSettings([...$event->getConfiguration()->getSettings(), 'normal_regen_min_percent' => 0, 'normal_regen_max_percent' => 0]);
        $this->random->shouldReceive('rand')->with(0, 0)->andReturn(0);

        $this->subject->regenerate($event, $ship, 1);

        foreach ([SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::PHASER, SpacecraftSystemTypeEnum::TORPEDO] as $type) {
            self::assertSame(0, $ship->getSpacecraftSystem($type)->getStatus());
            self::assertSame(SpacecraftSystemModeEnum::MODE_OFF, $ship->getSpacecraftSystem($type)->getMode());
        }
    }

    /** @return iterable<string, array{SpacecraftSystemModeEnum}> */
    public static function preservedModes(): iterable
    {
        yield 'permanently disabled system' => [SpacecraftSystemModeEnum::MODE_ALWAYS_OFF];
        yield 'permanently enabled system' => [SpacecraftSystemModeEnum::MODE_ALWAYS_ON];
        yield 'already enabled system' => [SpacecraftSystemModeEnum::MODE_ON];
    }

    #[DataProvider('preservedModes')]
    public function testRegenerationPreservesPermanentAndAlreadyActiveModes(SpacecraftSystemModeEnum $mode): void
    {
        $ship = $this->ship();
        $ship->getCondition()->setDisabled(false);
        foreach ([SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::PHASER, SpacecraftSystemTypeEnum::TORPEDO, SpacecraftSystemTypeEnum::SHIELDS, SpacecraftSystemTypeEnum::IMPULSEDRIVE] as $type) {
            $ship->getSystems()->set($type->value, new SpacecraftSystem()->setSpacecraft($ship)->setSystemType($type)->setStatus(10)->setMode($mode));
        }
        $this->random->shouldReceive('rand')->with(2, 10)->andReturn(2);

        $this->subject->regenerate($this->event()->setPhase(BorgSpherePhaseEnum::ROAMING), $ship, 1);

        foreach ([SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::PHASER, SpacecraftSystemTypeEnum::TORPEDO, SpacecraftSystemTypeEnum::SHIELDS, SpacecraftSystemTypeEnum::IMPULSEDRIVE] as $type) {
            self::assertSame(12, $ship->getSpacecraftSystem($type)->getStatus());
            self::assertSame($mode, $ship->getSpacecraftSystem($type)->getMode());
        }
    }

    public function testLongCatchupCapsStatsAndEscapeProgress(): void
    {
        $ship = $this->ship();
        $event = $this->event();
        $this->random->shouldReceive('rand')->with(2, 10)->times(500)->andReturn(10);
        $this->subject->regenerate($event, $ship, 100);
        self::assertSame(1000, $ship->getCondition()->getHull());
        self::assertSame(2000, $ship->getCondition()->getShield());
        self::assertSame(8000, $event->getTranswarpProgress());
        self::assertSame(7000, $event->getTranswarpFloor());
        self::assertSame(80, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRANSWARP_COIL)->getStatus());
        self::assertSame(100, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus());
        self::assertSame(100, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE)->getStatus());
    }

    /** @return iterable<string, array{string, int, bool}> */
    public static function wakeValues(): iterable
    {
        yield 'exactly ten percent hull' => ['hull', 100, false];
        yield 'hull above ten percent' => ['hull', 101, true];
        yield 'exactly ten percent shields' => ['shield', 200, false];
        yield 'shields above ten percent' => ['shield', 201, true];
        yield 'exactly ten percent system' => ['system', 10, false];
        yield 'system without module above ten percent' => ['system', 11, true];
        yield 'transwarp alone above ten percent' => ['transwarp', 79, false];
    }

    #[DataProvider('wakeValues')]
    public function testOneNonTranswarpValueMustBeStrictlyAboveTenPercent(string $value, int $amount, bool $expected): void
    {
        $ship = $this->ship();
        if ($value === 'hull') {
            $ship->getCondition()->setHull($amount);
        } elseif ($value === 'shield') {
            $ship->getCondition()->setShield($amount);
        } else {
            $type = $value === 'system' ? SpacecraftSystemTypeEnum::TRACTOR_BEAM : SpacecraftSystemTypeEnum::TRANSWARP_COIL;
            $ship->getSystems()->set($type->value, new SpacecraftSystem()->setSpacecraft($ship)->setSystemType($type)->setStatus($amount));
        }
        self::assertSame($expected, $this->getMethod($this->subject, 'isReady')->invoke($this->subject, $this->event(), $ship));
    }

    public function testNoDueHourDoesNotRegenerateOrWakeTheSphere(): void
    {
        $ship = $this->ship();
        $this->random->shouldNotReceive('rand');
        $this->subject->regenerate($this->event(), $ship, 0);
        self::assertSame(100, $ship->getCondition()->getHull());
        self::assertTrue($ship->getCondition()->isDisabled());
    }

    public function testBelowTenOnlyTheRolledAmountIsRestoredAndTranswarpAloneCannotWake(): void
    {
        $ship = $this->ship();
        foreach ([SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::PHASER, SpacecraftSystemTypeEnum::TORPEDO] as $type) {
            $ship->getSystems()->set($type->value, new SpacecraftSystem()->setSpacecraft($ship)->setSystemType($type)->setMode(SpacecraftSystemModeEnum::MODE_OFF));
        }
        $ship->getCondition()->setHull(0)->setShield(0);
        foreach ($ship->getSystems() as $system) {
            $system->setStatus(0);
        }
        $event = $this->event();
        $this->random->shouldReceive('rand')->with(2, 10)->times(8)->andReturn(2);
        $this->subject->regenerate($event, $ship, 1);
        self::assertSame(20, $ship->getCondition()->getHull());
        self::assertSame(40, $ship->getCondition()->getShield());
        self::assertSame(2, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus());
        self::assertSame(11, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRANSWARP_COIL)->getStatus());
        self::assertTrue($ship->getCondition()->isDisabled());
        self::assertSame(BorgSpherePhaseEnum::REGENERATING, $event->getPhase());
        foreach ([SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::PHASER, SpacecraftSystemTypeEnum::TORPEDO] as $type) {
            self::assertSame(2, $ship->getSpacecraftSystem($type)->getStatus());
            self::assertSame(SpacecraftSystemModeEnum::MODE_OFF, $ship->getSpacecraftSystem($type)->getMode());
        }
    }

    private function event(): BorgSphereEvent
    {
        $configuration = new BorgSphereConfiguration()->setSettings([
            'normal_regen_min_percent' => 2,
            'normal_regen_max_percent' => 10,
            'transwarp_regen_bp' => 100,
            'transwarp_escape_bp' => 8000,
            'transwarp_floor_step_bp' => 1000,
            'wake_percent' => 10
        ]);
        return new BorgSphereEvent()->setConfiguration($configuration)->setFirstDisabledAt(123)->setPhase(BorgSpherePhaseEnum::REGENERATING)->setTranswarpProgress(1000)->setTranswarpFloor(1000);
    }

    private function ship(): Ship
    {
        $ship = new Ship();
        $ship->setMaxHull(1000)->setMaxShield(2000);
        $ship->setCondition(new SpacecraftCondition($ship)->setHull(100)->setShield(200)->setDisabled(true));
        foreach ([SpacecraftSystemTypeEnum::SHIELDS, SpacecraftSystemTypeEnum::WARPDRIVE, SpacecraftSystemTypeEnum::TRANSWARP_COIL, SpacecraftSystemTypeEnum::TRACTOR_BEAM] as $type) {
            $system = new SpacecraftSystem()->setSpacecraft($ship)->setSystemType($type)->setStatus(10)->setMode(SpacecraftSystemModeEnum::MODE_OFF)->setModule(new Module());
            $ship->getSystems()->set($type->value, $system);
        }
        return $ship;
    }
}
