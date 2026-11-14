<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\StuRandom;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Module\Spacecraft\Lib\Battle\FightLibInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactory;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCycleInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactory;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Fleet;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

final class BorgTractorRescueResponseTest extends StuTestCase
{
    private SpacecraftWrapperFactoryInterface&MockInterface $wrappers;
    private SpacecraftAttackCycleInterface&MockInterface $cycle;
    private DistributedMessageSenderInterface&MockInterface $messages;
    private FightLibInterface&MockInterface $fight;
    private TholianWebUtilInterface&MockInterface $webs;
    private BorgSphereAttackContext $context;
    private BorgTractorRescueResponse $subject;
    private Ship $cube;
    private Ship $rescuer;
    private BorgSphereEvent $event;
    private Map $location;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->cycle = $this->mock(SpacecraftAttackCycleInterface::class);
        $this->messages = $this->mock(DistributedMessageSenderInterface::class);
        $this->fight = $this->mock(FightLibInterface::class);
        $this->webs = $this->mock(TholianWebUtilInterface::class);
        $this->webs->shouldReceive('isTargetOutsideFinishedTholianWeb', 'isTargetInsideFinishedTholianWeb')->andReturn(false)->byDefault();
        $this->context = new BorgSphereAttackContext();
        $parties = new BattlePartyFactory($this->wrappers, $this->mock(StuRandom::class), $this->context);
        $this->subject = new BorgTractorRescueResponse($this->wrappers, $parties, $this->cycle, $this->messages, $this->fight, $this->context, $this->webs);
        $this->location = new Map();
        $this->cube = $this->ship(1, 10);
        $this->rescuer = $this->ship(2, 101);
        $this->event = new BorgSphereEvent()->setShipId(1)->setPhase(BorgSpherePhaseEnum::ROAMING);
        $this->fight->shouldReceive('canAttackTarget')->with($this->cube, $this->rescuer, true)->andReturn(true)->byDefault();
    }

    public function testOneWayResponseIncludesOnlyLocalEligibleFleetMembersAndShowsTheWholeReport(): void
    {
        $source = $this->wrapper($this->cube);
        $target = $this->wrapper($this->rescuer);
        $sourceFleet = $this->fleet($this->cube, $source, 3, 10);
        $sourceFleet->remove($this->cube->getId());
        $sourceFleet->set($this->cube->getId(), $source);
        $targetFleet = $this->fleet($this->rescuer, $target, 20, 101);
        $foreign = $this->ship(9, 202);
        $sourceFleet->add($this->wrapper($foreign));
        $this->wrappers->shouldReceive('wrapShip')->once()->with($this->cube)->andReturn($source);
        $this->wrappers->shouldReceive('wrapShip')->once()->with($this->rescuer)->andReturn($target);
        $messages = new MessageFactory()->createMessageCollection();
        $messages->addInformation('Der Kubus feuert auf das Rettungsschiff');
        $messages->addInformation('- Schildschaden: 40');
        $messages->addInformation('Der assimilierte Begleiter feuert auf den Flottenbegleiter');
        $messages->addInformation('- Hüllenschaden: 60');
        $this->cycle->shouldReceive('cycle')->once()->andReturnUsing(function (BattlePartyInterface $attackers, BattlePartyInterface $defenders, SpacecraftAttackCauseEnum $cause) use ($messages): mixed {
            self::assertSame(SpacecraftAttackCauseEnum::BORG_TRACTOR_RESCUE_RESPONSE, $cause);
            self::assertTrue($cause->isOneWay());
            self::assertSame([1, 3], $attackers->getActiveMembers(true)->getKeys());
            self::assertSame([2, 20], $defenders->getActiveMembers()->getKeys());
            return $messages;
        });
        $this->messages->shouldReceive('distributeMessageCollection')->once()->with($messages, 10, PrivateMessageFolderTypeEnum::SPECIAL_SHIP, 'Kampf in Sektor ' . $this->cube->getSectorString());
        $information = new InformationWrapper();

        self::assertTrue($this->subject->respond($this->event, $this->cube, $this->rescuer, $information));

        self::assertSame($messages->getInformationDump()->getInformations(), $information->getInformations());
        self::assertTrue($this->context->allowsAutomaticAttackTarget($this->ship(99, 303)->setLocation(new Map())));
        self::assertCount(7, $sourceFleet);
        self::assertCount(6, $targetFleet);
    }

    /** @return iterable<string, array{string}> */
    public static function unavailableStates(): iterable
    {
        foreach (['wrong event ship', 'finished event', 'regenerating', 'cube disabled', 'cube destroyed', 'cube warped', 'cube cloaked', 'cube crew missing', 'rescuer remote', 'rescuer same owner', 'rescuer disabled', 'rescuer destroyed', 'rescuer warped', 'rescuer cloaked', 'rescuer invisible', 'rescuer on vacation'] as $state) {
            yield $state => [$state];
        }
    }

    #[DataProvider('unavailableStates')]
    public function testUnavailableResponseNeverCreatesASecondCombat(string $state): void
    {
        if ($state === 'wrong event ship') {
            $this->event->setShipId(99);
        } elseif ($state === 'finished event') {
            $this->event->setPhase(BorgSpherePhaseEnum::DESTROYED);
        } elseif ($state === 'regenerating') {
            $this->event->setPhase(BorgSpherePhaseEnum::REGENERATING);
        } elseif ($state === 'cube disabled') {
            $this->cube->getCondition()->setDisabled(true);
        } elseif ($state === 'cube destroyed') {
            $this->cube->getCondition()->setIsDestroyed(true);
        } elseif ($state === 'cube warped' || $state === 'rescuer warped') {
            $this->activate($state === 'cube warped' ? $this->cube : $this->rescuer, SpacecraftSystemTypeEnum::WARPDRIVE);
        } elseif ($state === 'cube cloaked' || $state === 'rescuer cloaked') {
            $this->activate($state === 'cube cloaked' ? $this->cube : $this->rescuer, SpacecraftSystemTypeEnum::CLOAK);
        } elseif ($state === 'cube crew missing') {
            $this->cube->getBuildplan()->setCrew(1);
        } elseif ($state === 'rescuer remote') {
            $this->rescuer->setLocation(new Map());
        } elseif ($state === 'rescuer same owner') {
            $this->rescuer->setUser($this->cube->getUser());
        } elseif ($state === 'rescuer disabled') {
            $this->rescuer->getCondition()->setDisabled(true);
        } elseif ($state === 'rescuer destroyed') {
            $this->rescuer->getCondition()->setIsDestroyed(true);
        } elseif ($state === 'rescuer invisible') {
            $this->activate($this->rescuer, SpacecraftSystemTypeEnum::RPG_MODULE)->setData('{"invisible":true}');
        } else {
            $this->rescuer->getUser()->setVacationMode(true)->setVacationRequestDate(1);
        }
        $this->wrappers->shouldNotReceive('wrapShip');
        $this->cycle->shouldNotReceive('cycle');
        $this->messages->shouldNotReceive('distributeMessageCollection');

        self::assertFalse($this->subject->respond($this->event, $this->cube, $this->rescuer, new InformationWrapper()));
    }

    public function testCubeWithoutFireCapabilityDoesNotRespond(): void
    {
        $source = $this->wrapper($this->cube, false);
        $this->wrappers->shouldReceive('wrapShip')->once()->with($this->cube)->andReturn($source);
        $this->cycle->shouldNotReceive('cycle');

        self::assertFalse($this->subject->respond($this->event, $this->cube, $this->rescuer, new InformationWrapper()));
    }

    /** @return iterable<string, array{string}> */
    public static function webSides(): iterable
    {
        yield 'outside the cube web' => ['isTargetOutsideFinishedTholianWeb'];
        yield 'inside another web' => ['isTargetInsideFinishedTholianWeb'];
    }

    #[DataProvider('webSides')]
    public function testFleetMembersAcrossACompletedWebCannotAttackOrBeHit(string $webCheck): void
    {
        $source = $this->wrapper($this->cube);
        $target = $this->wrapper($this->rescuer);
        $sourceFleet = $this->fleet($this->cube, $source, 3, 10);
        $targetFleet = $this->fleet($this->rescuer, $target, 20, 101);
        $this->webs->shouldReceive($webCheck)->with($this->cube, $sourceFleet->get(3)->get())->andReturn(true);
        $this->webs->shouldReceive($webCheck)->with($this->cube, $targetFleet->get(20)->get())->andReturn(true);
        $this->wrappers->shouldReceive('wrapShip')->with($this->cube)->andReturn($source);
        $this->wrappers->shouldReceive('wrapShip')->with($this->rescuer)->andReturn($target);
        $messages = new MessageFactory()->createMessageCollection();
        $this->cycle->shouldReceive('cycle')->once()->andReturnUsing(static function (BattlePartyInterface $attackers, BattlePartyInterface $defenders) use ($messages): mixed {
            self::assertSame([1], $attackers->getActiveMembers()->getKeys());
            self::assertSame([2], $defenders->getActiveMembers()->getKeys());
            self::assertSame(1, $attackers->getLeader()->get()->getId());
            self::assertSame(2, $defenders->getLeader()->get()->getId());
            return $messages;
        });
        $this->messages->shouldReceive('distributeMessageCollection')->once();

        self::assertTrue($this->subject->respond($this->event, $this->cube, $this->rescuer, new InformationWrapper()));
    }

    #[DataProvider('webSides')]
    public function testTheSelectedRescuerAcrossACompletedWebDoesNotReceiveAResponse(string $webCheck): void
    {
        $this->webs->shouldReceive($webCheck)->with($this->cube, $this->rescuer)->andReturn(true);
        $this->wrappers->shouldNotReceive('wrapShip');
        $this->cycle->shouldNotReceive('cycle');

        self::assertFalse($this->subject->respond($this->event, $this->cube, $this->rescuer, new InformationWrapper()));
    }

    public function testAutomaticContextIsClearedWhenTheCombatThrows(): void
    {
        $source = $this->wrapper($this->cube);
        $target = $this->wrapper($this->rescuer);
        $source->shouldReceive('getFleetWrapper')->andReturnNull();
        $this->wrappers->shouldReceive('wrapShip')->with($this->cube)->andReturn($source);
        $this->wrappers->shouldReceive('wrapShip')->with($this->rescuer)->andReturn($target);
        $this->cycle->shouldReceive('cycle')->once()->andThrow(new RuntimeException('battle failed'));
        $this->expectException(RuntimeException::class);
        try {
            $this->subject->respond($this->event, $this->cube, $this->rescuer, new InformationWrapper());
        } finally {
            self::assertTrue($this->context->allowsAutomaticAttackTarget($this->ship(99, 303)->setLocation(new Map())));
        }
    }

    /** @return ArrayCollection<int, ShipWrapperInterface> */
    private function fleet(Ship $leader, ShipWrapperInterface&MockInterface $leaderWrapper, int $firstId, int $userId): ArrayCollection
    {
        $leader->setFleet(new Fleet()->setLeadShip($leader))->setIsFleetLeader(true);
        $members = new ArrayCollection([$leader->getId() => $leaderWrapper]);
        foreach (range($firstId, $firstId + 4) as $id) {
            $ship = $this->ship($id, $userId);
            $ship->setFleet($leader->getFleet());
            if ($id === $firstId + 1) {
                $ship->setLocation(new Map());
            } elseif ($id === $firstId + 2) {
                $this->activate($ship, SpacecraftSystemTypeEnum::WARPDRIVE);
            } elseif ($id === $firstId + 3) {
                $this->activate($ship, SpacecraftSystemTypeEnum::CLOAK);
            } elseif ($id === $firstId + 4) {
                $ship->getCondition()->setDisabled(true);
            }
            $members->set($id, $this->wrapper($ship));
        }
        $fleet = $this->mock(FleetWrapperInterface::class);
        $fleet->shouldReceive('getShipWrappers')->andReturn($members);
        $leaderWrapper->shouldReceive('getFleetWrapper')->andReturn($fleet);
        return $members;
    }

    private function wrapper(Ship $ship, bool $canFire = true): ShipWrapperInterface&MockInterface
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $wrapper->shouldReceive('get')->andReturn($ship);
        $wrapper->shouldReceive('canFire')->andReturn($canFire);
        $wrapper->shouldReceive('getDockedToStationWrapper')->andReturnNull();
        $wrapper->shouldReceive('getFleetWrapper')->andReturnNull()->byDefault();
        return $wrapper;
    }

    private function ship(int $id, int $userId): Ship
    {
        $user = new User();
        new ReflectionProperty(User::class, 'id')->setValue($user, $userId);
        $ship = new Ship();
        new ReflectionProperty(Spacecraft::class, 'id')->setValue($ship, $id);
        $ship->setUser($user)->setLocation($this->location)->setBuildplan(new SpacecraftBuildplan()->setCrew(0))
            ->setCondition(new SpacecraftCondition($ship))->setRump(new SpacecraftRump()->setCategoryId(SpacecraftRumpCategoryEnum::WARSHIP));
        return $ship;
    }

    private function activate(Ship $ship, SpacecraftSystemTypeEnum $type): SpacecraftSystem
    {
        $system = new SpacecraftSystem()->setSpacecraft($ship)->setSystemType($type)->setMode(SpacecraftSystemModeEnum::MODE_ON);
        $ship->getSystems()->set($type->value, $system);
        return $system;
    }
}
