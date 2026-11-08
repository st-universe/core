<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Override;
use ReflectionProperty;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\StuTestCase;

final class BorgSphereCombatTest extends StuTestCase
{
    private Ship $ship;
    private BorgSphereEvent $event;
    private BorgSphereAttackContext $context;
    private BorgSphereCombat $combat;

    #[Override]
    public function setUp(): void
    {
        $this->ship = new Ship();
        new ReflectionProperty(Spacecraft::class, 'id')->setValue($this->ship, 101);
        $this->ship->setMaxHull(1000)->setMaxShield(2000);
        $this->ship->setCondition(new SpacecraftCondition($this->ship)->setHull(100)->setShield(0));
        foreach ([SpacecraftSystemTypeEnum::SHIELDS, SpacecraftSystemTypeEnum::EPS, SpacecraftSystemTypeEnum::TRANSWARP_COIL] as $type) {
            $this->ship->getSystems()->set($type->value, new SpacecraftSystem()->setSystemType($type)->setStatus(10)->setMode(SpacecraftSystemModeEnum::MODE_ON));
        }
        $configuration = new BorgSphereConfiguration()->setSettings(['tick_seconds' => 3600, 'transwarp_initial_bp' => 1000]);
        $this->event = new BorgSphereEvent()->setShipId(101)->setConfiguration($configuration);
        $repository = $this->mock(BorgSphereRepositoryInterface::class);
        $repository->shouldReceive('getByShip')->with($this->ship)->andReturn($this->event);
        $repository->shouldReceive('lockEvent')->with($this->event)->once();
        $repository->shouldReceive('save')->with($this->event);
        $time = $this->mock(StuTime::class);
        $time->shouldReceive('time')->andReturn(1000);
        $this->context = new BorgSphereAttackContext();
        $this->combat = new BorgSphereCombat($repository, $this->context, $time);
    }

    public function testHullStopsAtTenPercentWhileSystemsStillNeedToBeDamaged(): void
    {
        $this->ship->getCondition()->setHull(120);
        $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::EPS)->setStatus(11);
        $information = new InformationWrapper();
        self::assertSame(20, $this->combat->limitHullDamage($this->ship, 500));
        $this->ship->getCondition()->setHull(100);
        $this->combat->finishHit($this->ship, $information);
        self::assertFalse($this->ship->getCondition()->isDisabled());
        self::assertNull($this->event->getFirstDisabledAt());
        self::assertSame(10, $this->combat->limitSystemStatus($this->ship, $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::EPS), -50));
    }

    public function testFinalSystemDisablesSphereAndStopsRemainingFleetSalvos(): void
    {
        $information = new InformationWrapper();
        $this->combat->finishHit($this->ship, $information);
        self::assertTrue($this->ship->getCondition()->isDisabled());
        self::assertSame(BorgSpherePhaseEnum::REGENERATING, $this->event->getPhase());
        self::assertSame(1000, $this->event->getFirstDisabledAt());
        self::assertSame(4600, $this->event->getNextTickAt());
        self::assertSame(200, $this->ship->getCondition()->getShield());
        self::assertSame(1000, $this->event->getTranswarpProgress());
        self::assertTrue($this->combat->blocksDamage($this->ship));
        self::assertSame(SpacecraftSystemModeEnum::MODE_OFF, $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)->getMode());
        self::assertSame(SpacecraftSystemModeEnum::MODE_ON, $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::EPS)->getMode());
    }

    public function testExplicitConfirmationAllowsHullDestructionOnlyForThisTarget(): void
    {
        $this->combat->finishHit($this->ship, new InformationWrapper());
        $this->context->begin($this->ship, true, false);
        self::assertFalse($this->combat->blocksDamage($this->ship));
        self::assertSame(500, $this->combat->limitHullDamage($this->ship, 500));
        $this->context->clear();
        self::assertTrue($this->combat->blocksDamage($this->ship));
    }

    public function testTranswarpAdaptationDoesNotReducePreviouslyReachedFloor(): void
    {
        $this->event->setFirstDisabledAt(100)->setTranswarpFloor(4000)->setTranswarpProgress(4700);
        $system = $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRANSWARP_COIL)->setStatus(47);
        self::assertSame(40, $this->combat->limitSystemStatus($this->ship, $system, 0));
        self::assertSame(4000, $this->event->getTranswarpProgress());
        $system->setStatus(40);
        self::assertFalse($this->combat->canDamageSystem($this->ship, $system));
        $this->combat->finishHit($this->ship, new InformationWrapper());
        self::assertSame(4000, $this->event->getTranswarpFloor());
        self::assertSame(4000, $this->event->getTranswarpProgress());
        self::assertSame(100, $this->event->getFirstDisabledAt());
    }

    public function testFirstHitEndsApproachAndTargetedFireSelectsOnlyTranswarp(): void
    {
        $this->context->begin($this->ship, false, true);
        self::assertTrue($this->combat->registerHit($this->ship));
        self::assertSame(BorgSpherePhaseEnum::ROAMING, $this->event->getPhase());
        self::assertSame(SpacecraftSystemTypeEnum::TRANSWARP_COIL, $this->combat->targetSystems($this->ship));
        $this->context->clear();
        self::assertNull($this->combat->targetSystems($this->ship));
    }
}
