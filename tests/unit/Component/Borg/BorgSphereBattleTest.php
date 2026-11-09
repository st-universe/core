<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCoreInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

final class BorgSphereBattleTest extends StuTestCase
{
    private SpacecraftAttackCoreInterface&MockInterface $attacks;
    private SpacecraftWrapperFactoryInterface&MockInterface $wrappers;
    private PlayerRelationDeterminatorInterface&MockInterface $relations;
    private FightLibInterface&MockInterface $fight;
    private BorgSphereAttackContext $context;
    private BorgSphereBattle $subject;
    private StarSystemMap $location;
    private Ship $sphere;
    private BorgSphereEvent $event;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->attacks = $this->mock(SpacecraftAttackCoreInterface::class);
        $this->wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->relations = $this->mock(PlayerRelationDeterminatorInterface::class);
        $this->fight = $this->mock(FightLibInterface::class);
        $this->context = new BorgSphereAttackContext();
        $this->subject = new BorgSphereBattle($this->attacks, $this->wrappers, $this->relations, $this->fight, $this->context);
        $this->location = new StarSystemMap();
        $this->sphere = $this->ship(1, 19);
        $this->event = new BorgSphereEvent()->setPhase(BorgSpherePhaseEnum::ROAMING);
    }

    public function testAllForeignNonFriendsAtThisLocationAreAttackedOncePerFleet(): void
    {
        $own = $this->ship(2, 19);
        $this->ship(3, 22);
        $cloaked = $this->ship(4, 23);
        $this->activate($cloaked, SpacecraftSystemTypeEnum::CLOAK);
        $warped = $this->ship(5, 24);
        $this->activate($warped, SpacecraftSystemTypeEnum::WARPDRIVE);
        $destroyed = $this->ship(6, 25);
        $destroyed->getCondition()->setIsDestroyed(true);
        $pods = $this->ship(7, 26);
        $pods->getRump()->setCategoryId(SpacecraftRumpCategoryEnum::ESCAPE_PODS);
        $vacation = $this->ship(8, 27);
        $vacation->getUser()->setVacationMode(true)->setVacationRequestDate(1);
        $remote = $this->ship(9, 28);
        $remote->setLocation(new StarSystemMap());
        $invisible = $this->ship(14, 29);
        $this->activate($invisible, SpacecraftSystemTypeEnum::RPG_MODULE);
        $invisible->getSpacecraftSystem(SpacecraftSystemTypeEnum::RPG_MODULE)->setData('{"invisible":true}');
        $station = new Station();
        $station->setUser($own->getUser());
        $this->location->getSpacecrafts()->add($station);
        $first = $this->ship(10, 261);
        $second = $this->ship(11, 262);
        $fleetShip = $this->ship(12, 263);
        $fleetMate = $this->ship(13, 263);
        $fleet = new Fleet();
        new ReflectionProperty(Fleet::class, 'id')->setValue($fleet, 99);
        $fleetShip->setFleet($fleet);
        $fleetMate->setFleet($fleet);
        $this->relations->shouldReceive('isFriend')->andReturnUsing(static fn (User $source, User $target): bool => $target->getId() === 22);
        $this->relations->shouldNotReceive('isEnemy');
        $this->fight->shouldReceive('canAttackTarget')->with($this->sphere, $first, true)->once()->andReturn(true);
        $this->fight->shouldReceive('canAttackTarget')->with($this->sphere, $second, true)->once()->andReturn(true);
        $this->fight->shouldReceive('canAttackTarget')->with($this->sphere, $fleetShip, true)->once()->andReturn(true);
        $source = $this->wrapper($this->sphere);
        foreach ([$first, $second, $fleetShip] as $target) {
            $wrapper = $this->wrapper($target);
            $this->attacks->shouldReceive('attack')->once()->with($source, $wrapper, false, false, InformationWrapper::class)
                ->andReturnUsing(static function ($source, $target, bool $shieldsOnly, bool &$fleetFight): bool {
                    $fleetFight = $target->get()->getFleetId() !== null;
                    if ($fleetFight) {
                        $target->get()->setFleet(null);
                    }
                    return true;
                });
        }

        self::assertTrue($this->subject->attackTargets($this->event, $this->sphere));
    }

    public function testCounterfireDisablingTheSphereStopsFurtherTargetsAndClearsTheScope(): void
    {
        $target = $this->ship(2, 261);
        $this->ship(3, 262);
        $source = $this->wrapper($this->sphere);
        $wrapper = $this->wrapper($target);
        $this->relations->shouldReceive('isFriend')->once()->andReturn(false);
        $this->fight->shouldReceive('canAttackTarget')->once()->andReturn(true);
        $this->attacks->shouldReceive('attack')->once()->with($source, $wrapper, false, false, InformationWrapper::class)
            ->andReturnUsing(function (): bool {
                $this->sphere->getCondition()->setDisabled(true);
                $this->event->setPhase(BorgSpherePhaseEnum::REGENERATING);
                $this->context->stopBattleAfterDisabling($this->sphere);
                return true;
            });

        self::assertTrue($this->subject->attackTargets($this->event, $this->sphere));

        self::assertFalse($this->context->isBattleStopped());
        self::assertTrue($this->context->allowsAutomaticAttackTarget($this->ship(4, 263)->setLocation(new StarSystemMap())));
    }

    /** @return iterable<string, array{string}> */
    public static function inactiveStates(): iterable
    {
        foreach (['disabled', 'destroyed', 'regenerating', 'escaped', 'warp', 'cloak', 'crew'] as $state) {
            yield $state => [$state];
        }
    }

    #[DataProvider('inactiveStates')]
    public function testUnavailableSphereDoesNotInitiateAnAttack(string $state): void
    {
        if ($state === 'disabled') {
            $this->sphere->getCondition()->setDisabled(true);
        } elseif ($state === 'destroyed') {
            $this->sphere->getCondition()->setIsDestroyed(true);
        } elseif ($state === 'regenerating' || $state === 'escaped') {
            $this->event->setPhase($state === 'escaped' ? BorgSpherePhaseEnum::ESCAPED : BorgSpherePhaseEnum::REGENERATING);
        } elseif ($state === 'crew') {
            $this->sphere->setBuildplan(new SpacecraftBuildplan()->setCrew(1));
        } else {
            $this->activate($this->sphere, $state === 'warp' ? SpacecraftSystemTypeEnum::WARPDRIVE : SpacecraftSystemTypeEnum::CLOAK);
        }
        $this->wrappers->shouldNotReceive('wrapShip');
        $this->attacks->shouldNotReceive('attack');

        self::assertFalse($this->subject->attackTargets($this->event, $this->sphere));
    }

    public function testNoSuitableTargetReportsThatNoAttackWasPerformed(): void
    {
        $this->wrapper($this->sphere);
        $this->attacks->shouldNotReceive('attack');

        self::assertFalse($this->subject->attackTargets($this->event, $this->sphere));
    }

    private function ship(int $id, int $userId): Ship
    {
        $user = new User();
        new ReflectionProperty(User::class, 'id')->setValue($user, $userId);
        $ship = new Ship();
        new ReflectionProperty(Spacecraft::class, 'id')->setValue($ship, $id);
        $ship->setUser($user)->setLocation($this->location)->setBuildplan(new SpacecraftBuildplan()->setCrew(0))
            ->setCondition(new SpacecraftCondition($ship))->setRump(new SpacecraftRump()->setCategoryId(SpacecraftRumpCategoryEnum::WARSHIP));
        $this->location->getSpacecrafts()->add($ship);
        return $ship;
    }

    private function wrapper(Ship $ship): ShipWrapperInterface
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $wrapper->shouldReceive('get')->andReturn($ship);
        $wrapper->shouldReceive('canFire')->andReturn(true);
        $this->wrappers->shouldReceive('wrapShip')->once()->with($ship)->andReturn($wrapper);
        return $wrapper;
    }

    private function activate(Ship $ship, SpacecraftSystemTypeEnum $type): void
    {
        $ship->getSystems()->set($type->value, new SpacecraftSystem()->setSystemType($type)->setStatus(100)->setMode(SpacecraftSystemModeEnum::MODE_ON));
    }
}
