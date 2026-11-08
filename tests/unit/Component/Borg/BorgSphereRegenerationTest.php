<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\StuTestCase;

final class BorgSphereRegenerationTest extends StuTestCase
{
    public function testDoesNotRegenerateBeforeFirstDisable(): void
    {
        $ship = $this->ship();
        $event = $this->event()->setFirstDisabledAt(null);
        new BorgSphereRegeneration()->regenerate($event, $ship, 10);
        self::assertSame(100, $ship->getCondition()->getHull());
        self::assertSame(1000, $event->getTranswarpProgress());
    }

    public function testHourlyRegenerationUsesFullShieldCapacityAndReachedTranswarpFloors(): void
    {
        $ship = $this->ship();
        $event = $this->event()->setTranswarpProgress(1900);
        new BorgSphereRegeneration()->regenerate($event, $ship, 1);
        self::assertSame(120, $ship->getCondition()->getHull());
        self::assertSame(240, $ship->getCondition()->getShield());
        self::assertSame(12, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getStatus());
        self::assertSame(2000, $event->getTranswarpProgress());
        self::assertSame(2000, $event->getTranswarpFloor());
        self::assertTrue($ship->getCondition()->isDisabled());
    }

    public function testWakesOnlyWhenHullShieldsAndModuleSystemsAreAtEightyPercent(): void
    {
        $ship = $this->ship();
        $event = $this->event();
        new BorgSphereRegeneration()->regenerate($event, $ship, 34);
        self::assertTrue($ship->getCondition()->isDisabled());
        self::assertSame(BorgSpherePhaseEnum::REGENERATING, $event->getPhase());
        new BorgSphereRegeneration()->regenerate($event, $ship, 1);
        self::assertFalse($ship->getCondition()->isDisabled());
        self::assertSame(BorgSpherePhaseEnum::ROAMING, $event->getPhase());
        self::assertSame(800, $ship->getCondition()->getHull());
        self::assertSame(1600, $ship->getCondition()->getShield());
        self::assertSame(SpacecraftSystemModeEnum::MODE_ON, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getMode());
        self::assertSame(SpacecraftSystemModeEnum::MODE_OFF, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::WARPDRIVE)->getMode());
        self::assertSame(SpacecraftSystemModeEnum::MODE_OFF, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->getMode());
    }

    public function testLongCatchupCapsStatsAndEscapeProgress(): void
    {
        $ship = $this->ship();
        $event = $this->event();
        new BorgSphereRegeneration()->regenerate($event, $ship, 1000);
        self::assertSame(1000, $ship->getCondition()->getHull());
        self::assertSame(2000, $ship->getCondition()->getShield());
        self::assertSame(8000, $event->getTranswarpProgress());
        self::assertSame(7000, $event->getTranswarpFloor());
        self::assertSame(80, $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRANSWARP_COIL)->getStatus());
    }

    private function event(): BorgSphereEvent
    {
        $configuration = new BorgSphereConfiguration()->setSettings([
            'normal_regen_percent' => 2,
            'transwarp_regen_bp' => 100,
            'transwarp_escape_bp' => 8000,
            'transwarp_floor_step_bp' => 1000,
            'wake_percent' => 80
        ]);
        return new BorgSphereEvent()->setConfiguration($configuration)->setFirstDisabledAt(123)->setPhase(BorgSpherePhaseEnum::REGENERATING)->setTranswarpProgress(1000)->setTranswarpFloor(1000);
    }

    private function ship(): Ship
    {
        $ship = new Ship()->setMaxHull(1000)->setMaxShield(2000);
        $ship->setCondition(new SpacecraftCondition($ship)->setHull(100)->setShield(200)->setDisabled(true));
        foreach ([SpacecraftSystemTypeEnum::SHIELDS, SpacecraftSystemTypeEnum::WARPDRIVE, SpacecraftSystemTypeEnum::TRANSWARP_COIL, SpacecraftSystemTypeEnum::TRACTOR_BEAM] as $type) {
            $system = new SpacecraftSystem()->setSpacecraft($ship)->setSystemType($type)->setStatus(10)->setMode(SpacecraftSystemModeEnum::MODE_OFF)->setModule(new Module());
            $ship->getSystems()->set($type->value, $system);
        }
        return $ship;
    }
}
