<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactory;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCycleInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactory;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\TholianWeb;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\StuTestCase;

final class BorgWebReactionTest extends StuTestCase
{
    private BorgSphereRepositoryInterface&MockInterface $events;
    private SpacecraftWrapperFactoryInterface&MockInterface $wrappers;
    private SpacecraftAttackCycleInterface&MockInterface $cycle;
    private DistributedMessageSenderInterface&MockInterface $messages;
    private StuRandom&MockInterface $random;
    private BorgWebReaction $subject;
    private BorgSphereEvent $event;
    private Ship $cube;
    private TholianWeb $web;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->events = $this->mock(BorgSphereRepositoryInterface::class);
        $this->wrappers = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->cycle = $this->mock(SpacecraftAttackCycleInterface::class);
        $this->messages = $this->mock(DistributedMessageSenderInterface::class);
        $this->random = $this->mock(StuRandom::class);
        $clock = $this->mock(StuTime::class);
        $clock->shouldReceive('time')->andReturn(1000);
        $parties = new BattlePartyFactory($this->wrappers, $this->mock(StuRandom::class));
        $this->subject = new BorgWebReaction($this->events, $this->wrappers, $parties, $this->cycle, $this->messages, $this->random, $clock);
        $user = new User();
        new ReflectionProperty(User::class, 'id')->setValue($user, 10);
        $location = new Map();
        $this->cube = new Ship()->setUser($user)->setLocation($location)->setName('Kubus');
        new ReflectionProperty(Spacecraft::class, 'id')->setValue($this->cube, 77);
        $this->cube->setCondition(new SpacecraftCondition($this->cube))->setBuildplan(new SpacecraftBuildplan()->setCrew(0));
        $this->web = new TholianWeb()->setUser(new User())->setLocation($location);
        $this->web->setCondition(new SpacecraftCondition($this->web));
        $this->cube->setHoldingWeb($this->web);
        $this->web->getCapturedSpacecrafts()->add($this->cube);
        $configuration = new BorgSphereConfiguration()->setUser($user)->setQuest(new NPCQuest()->setStart(900))->setEndTime(2000);
        $this->event = new BorgSphereEvent()->setConfiguration($configuration)->setShipId(77)->setPhase(BorgSpherePhaseEnum::ROAMING);
    }

    public function testTwentyFivePercentReactionUsesOneWaySingletonPartiesAndShowsTheReport(): void
    {
        $this->expectAvailable();
        $source = $this->wrapper();
        $target = $this->mock(SpacecraftWrapperInterface::class);
        $target->shouldReceive('get')->andReturn($this->web);
        $this->wrappers->shouldReceive('wrapSpacecraft')->once()->with($this->web)->andReturn($target);
        $this->random->shouldReceive('rand')->once()->with(1, 100)->andReturn(25);
        $messages = new MessageFactory()->createMessageCollection();
        $messages->addInformation('Der Kubus feuert auf das Energienetz');
        $messages->addInformation('- Hüllenschaden: 100');
        $this->cycle->shouldReceive('cycle')->once()->andReturnUsing(function (BattlePartyInterface $attackers, BattlePartyInterface $defenders, SpacecraftAttackCauseEnum $cause) use ($source, $target, $messages): mixed {
            self::assertSame(SpacecraftAttackCauseEnum::BORG_WEB_REACTION, $cause);
            self::assertTrue($cause->isOneWay());
            self::assertSame($source, $attackers->getLeader());
            self::assertSame($target, $defenders->getLeader());
            self::assertCount(1, $attackers->getActiveMembers());
            self::assertCount(1, $defenders->getActiveMembers());
            return $messages;
        });
        $this->messages->shouldReceive('distributeMessageCollection')->once()->with($messages, 10, PrivateMessageFolderTypeEnum::SPECIAL_SHIP, 'Kampf am Energienetz in Sektor ' . $this->cube->getSectorString());
        $information = new InformationWrapper();

        $this->subject->react($this->web, $information);

        self::assertSame($messages->getInformationDump()->getInformations(), $information->getInformations());
    }

    public function testTwentySixDoesNotReactButALaterSupportCanTryAgain(): void
    {
        $this->expectAvailable(2);
        $this->wrapper();
        $target = $this->mock(SpacecraftWrapperInterface::class);
        $target->shouldReceive('get')->andReturn($this->web);
        $this->wrappers->shouldReceive('wrapSpacecraft')->once()->with($this->web)->andReturn($target);
        $this->random->shouldReceive('rand')->twice()->with(1, 100)->andReturn(26, 25);
        $messages = new MessageFactory()->createMessageCollection();
        $messages->addInformation('Das Energienetz wird beschossen');
        $this->cycle->shouldReceive('cycle')->once()->andReturn($messages);
        $this->messages->shouldReceive('distributeMessageCollection')->once();
        $information = new InformationWrapper();

        $this->subject->react($this->web, $information);
        self::assertSame([], $information->getInformations());
        $this->subject->react($this->web, $information);

        self::assertSame(['Das Energienetz wird beschossen'], $information->getInformations());
    }

    public function testConfigurationCanOverrideTheRetaliationChance(): void
    {
        $this->event->getConfiguration()->setSettings(['web_retaliation_chance_percent' => 40]);
        $this->expectAvailable();
        $this->wrapper();
        $this->random->shouldReceive('rand')->once()->with(1, 100)->andReturn(41);
        $this->cycle->shouldNotReceive('cycle');

        $this->subject->react($this->web, new InformationWrapper());
    }

    public function testShipWithoutFireCapabilityDoesNotRoll(): void
    {
        $this->expectAvailable();
        $this->wrapper(false);
        $this->random->shouldNotReceive('rand');
        $this->cycle->shouldNotReceive('cycle');

        $this->subject->react($this->web, new InformationWrapper());
    }

    /** @return iterable<string, array{string}> */
    public static function invalidStates(): iterable
    {
        foreach (['wrong event ship', 'wrong owner', 'finished event', 'regenerating', 'future start', 'expired', 'quest completed', 'no event', 'disabled', 'destroyed', 'web destroyed', 'another web', 'remote', 'warp', 'cloak', 'missing crew'] as $state) {
            yield $state => [$state];
        }
    }

    #[DataProvider('invalidStates')]
    public function testUnavailableShipsNeverRollOrFire(string $state): void
    {
        $this->invalidate($state);
        $this->events->shouldReceive('getByShip')->with($this->cube)->andReturn($state === 'no event' ? null : $this->event);
        $this->events->shouldReceive('lockEvent')->with($this->event);
        $this->wrappers->shouldNotReceive('wrapShip', 'wrapSpacecraft');
        $this->random->shouldNotReceive('rand');
        $this->cycle->shouldNotReceive('cycle');
        $this->messages->shouldNotReceive('distributeMessageCollection');

        $this->subject->react($this->web, new InformationWrapper());
    }

    private function invalidate(string $state): void
    {
        $foreignUser = new User();
        new ReflectionProperty(User::class, 'id')->setValue($foreignUser, 99);
        match ($state) {
            'wrong event ship' => $this->event->setShipId(99),
            'wrong owner' => $this->cube->setUser($foreignUser),
            'finished event' => $this->event->setPhase(BorgSpherePhaseEnum::DESTROYED),
            'regenerating' => $this->event->setPhase(BorgSpherePhaseEnum::REGENERATING),
            'future start' => $this->event->getConfiguration()->getQuest()->setStart(1001),
            'expired' => $this->event->getConfiguration()->setEndTime(1000),
            'quest completed' => $this->event->getConfiguration()->getQuest()->setEnd(999),
            'disabled' => $this->cube->getCondition()->setDisabled(true),
            'destroyed' => $this->cube->getCondition()->setIsDestroyed(true),
            'web destroyed' => $this->web->getCondition()->setIsDestroyed(true),
            'another web' => $this->cube->setHoldingWeb(new TholianWeb()),
            'remote' => $this->cube->setLocation(new Map()),
            'warp' => $this->activate(SpacecraftSystemTypeEnum::WARPDRIVE),
            'cloak' => $this->activate(SpacecraftSystemTypeEnum::CLOAK),
            'missing crew' => $this->cube->getBuildplan()->setCrew(1),
            default => null
        };
    }

    private function expectAvailable(int $times = 1): void
    {
        $this->events->shouldReceive('getByShip')->times($times)->with($this->cube)->andReturn($this->event);
        $this->events->shouldReceive('lockEvent')->times($times)->with($this->event);
    }

    private function wrapper(bool $canFire = true): ShipWrapperInterface&MockInterface
    {
        $wrapper = $this->mock(ShipWrapperInterface::class);
        $wrapper->shouldReceive('get')->andReturn($this->cube);
        $wrapper->shouldReceive('canFire')->andReturn($canFire);
        $this->wrappers->shouldReceive('wrapShip')->with($this->cube)->andReturn($wrapper);
        return $wrapper;
    }

    private function activate(SpacecraftSystemTypeEnum $type): void
    {
        $this->cube->getSystems()->set($type->value, new SpacecraftSystem()->setSpacecraft($this->cube)->setSystemType($type)->setStatus(100)->setMode(SpacecraftSystemModeEnum::MODE_ON));
    }
}
