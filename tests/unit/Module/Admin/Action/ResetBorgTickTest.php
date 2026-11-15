<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use request;
use Stu\Component\Borg\BorgSpherePhaseEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Stu\StuTestCase;

final class ResetBorgTickTest extends StuTestCase
{
    private BorgSphereRepositoryInterface&MockInterface $events;
    private StuTime&MockInterface $time;
    private ActionControllerContext&MockInterface $context;
    private InformationWrapper $information;
    private ResetBorgTick $subject;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        request::setMockVars(['borg_configuration_id' => 2]);
        $this->events = $this->mock(BorgSphereRepositoryInterface::class);
        $this->time = $this->mock(StuTime::class);
        $this->context = $this->mock(ActionControllerContext::class);
        $this->information = new InformationWrapper();
        $this->context->shouldReceive('getInfo')->andReturn($this->information);
        $this->context->shouldReceive('setView')->once()->with(ShowScripts::VIEW_IDENTIFIER);
        $this->subject = new ResetBorgTick($this->events, $this->time);
    }

    public function testUnknownConfigurationDoesNotLookUpOrCreateAnEvent(): void
    {
        $this->events->shouldReceive('findConfiguration')->once()->with(2)->andReturnNull();
        $this->events->shouldNotReceive('getByConfiguration', 'lockEvent', 'save');

        $this->subject->handle($this->context);

        self::assertSame(['Die Borg-Konfiguration wurde nicht gefunden'], $this->information->getInformations());
    }

    public function testUnspawnedConfigurationDoesNotCreateAnEvent(): void
    {
        $configuration = $this->configuration();
        $this->events->shouldReceive('findConfiguration')->once()->with(2)->andReturn($configuration);
        $this->events->shouldReceive('getByConfiguration')->once()->with($configuration)->andReturnNull();
        $this->events->shouldNotReceive('lockEvent', 'save');

        $this->subject->handle($this->context);

        self::assertSame(['Das Borg-Schiff dieser Konfiguration wurde noch nicht gespawnt'], $this->information->getInformations());
    }

    public function testEventWithoutAnExistingShipIsNotRescheduled(): void
    {
        $event = new BorgSphereEvent()->setConfiguration($this->configuration())->setNextTickAt(500);
        $this->expectEvent($event);
        $this->events->shouldReceive('lockEvent')->once()->with($event);
        $this->events->shouldNotReceive('save');

        $this->subject->handle($this->context);

        self::assertSame(500, $event->getNextTickAt());
        self::assertSame(['Dieses Borg-Event ist bereits beendet'], $this->information->getInformations());
    }

    /** @return iterable<string, array{BorgSpherePhaseEnum, bool}> */
    public static function finishedEvents(): iterable
    {
        yield 'escaped' => [BorgSpherePhaseEnum::ESCAPED, false];
        yield 'destroyed' => [BorgSpherePhaseEnum::DESTROYED, false];
        yield 'event ends while its lock is acquired' => [BorgSpherePhaseEnum::DESTROYED, true];
    }

    #[DataProvider('finishedEvents')]
    public function testFinishedEventsAreRejectedAfterLocking(BorgSpherePhaseEnum $phase, bool $duringLock): void
    {
        $event = new BorgSphereEvent()->setConfiguration($this->configuration())->setShipId(77)->setNextTickAt(500)->setPhase($duringLock ? BorgSpherePhaseEnum::ROAMING : $phase);
        $this->expectEvent($event);
        $this->events->shouldReceive('lockEvent')->once()->with($event)->andReturnUsing(static fn () => $event->setPhase($phase));
        $this->events->shouldNotReceive('save');

        $this->subject->handle($this->context);

        self::assertSame(500, $event->getNextTickAt());
        self::assertSame(['Dieses Borg-Event ist bereits beendet'], $this->information->getInformations());
    }

    /** @return iterable<string, array{string}> */
    public static function invalidConfigurations(): iterable
    {
        foreach (['not started', 'quest completed', 'expired', 'missing interval', 'zero interval', 'negative interval'] as $state) {
            yield $state => [$state];
        }
    }

    #[DataProvider('invalidConfigurations')]
    public function testInvalidConfigurationDoesNotChangeTheTimer(string $state): void
    {
        $configuration = $this->configuration();
        match ($state) {
            'not started' => $configuration->getQuest()->setStart(1001),
            'quest completed' => $configuration->getQuest()->setEnd(999),
            'expired' => $configuration->setEndTime(1000),
            'missing interval' => $configuration->setSettings([]),
            'zero interval' => $configuration->setSettings(['tick_seconds' => 0]),
            'negative interval' => $configuration->setSettings(['tick_seconds' => -1])
        };
        $event = new BorgSphereEvent()->setConfiguration($configuration)->setShipId(77)->setNextTickAt(500);
        $this->expectEvent($event);
        $this->events->shouldReceive('lockEvent')->once()->with($event);
        $this->events->shouldNotReceive('save');
        $this->time->shouldReceive('time')->once()->andReturn(1000);

        $this->subject->handle($this->context);

        self::assertSame(500, $event->getNextTickAt());
        self::assertSame(['Für dieses Borg-Event ist keine gültige aktive Konfiguration vorhanden'], $this->information->getInformations());
    }

    /** @return iterable<string, array{BorgSpherePhaseEnum}> */
    public static function runningPhases(): iterable
    {
        yield 'approach' => [BorgSpherePhaseEnum::APPROACH];
        yield 'roaming' => [BorgSpherePhaseEnum::ROAMING];
        yield 'regenerating' => [BorgSpherePhaseEnum::REGENERATING];
    }

    #[DataProvider('runningPhases')]
    public function testOnlyNextTickChangesAndAnActiveAssimilationKeepsItsOriginalSchedule(BorgSpherePhaseEnum $phase): void
    {
        $configuration = $this->configuration()->setSettings(['tick_seconds' => 1800]);
        $event = new BorgSphereEvent()->setConfiguration($configuration)->setShipId(77)->setPhase($phase)->setNextTickAt(500)
            ->setFirstDisabledAt(200)->setTranswarpProgress(2100)->setTranswarpFloor(2000)->setInitialAttackDone(true);
        $user = new User();
        new ReflectionProperty(User::class, 'id')->setValue($user, 10);
        $cube = new Ship()->setUser($user);
        $target = new Ship();
        $entry = new SpacecraftAssimilated()->setSpacecraft($target)->start($cube, 700, 900)->setProgress(3333);
        $this->expectEvent($event);
        $this->events->shouldReceive('lockEvent')->once()->with($event)->ordered();
        $this->events->shouldReceive('save')->once()->with($event)->ordered();
        $this->time->shouldReceive('time')->once()->andReturn(1000);
        $this->time->shouldReceive('transformToStuDateTime')->once()->with(2800)->andReturn('15.11.2396 01:46');

        $this->subject->handle($this->context);

        self::assertSame(2800, $event->getNextTickAt());
        self::assertSame($phase, $event->getPhase());
        self::assertSame(200, $event->getFirstDisabledAt());
        self::assertSame(2100, $event->getTranswarpProgress());
        self::assertSame(2000, $event->getTranswarpFloor());
        self::assertTrue($event->isInitialAttackDone());
        self::assertSame(77, $event->getShipId());
        self::assertSame(700, $entry->getStartedAt());
        self::assertSame(900, $entry->getDurationSeconds());
        self::assertSame(3333, $entry->getProgress());
        self::assertTrue($entry->isActive());
        self::assertSame(['Nächste Borg-Aktion: 15.11.2396 01:46 Uhr'], $this->information->getInformations());
        self::assertTrue($this->subject->performSessionCheck());
    }

    private function configuration(): BorgSphereConfiguration
    {
        return new BorgSphereConfiguration()->setId(2)->setQuest(new NPCQuest()->setStart(0))->setEndTime(10000)->setSettings(['tick_seconds' => 3600]);
    }

    private function expectEvent(BorgSphereEvent $event): void
    {
        $this->events->shouldReceive('findConfiguration')->once()->with(2)->andReturn($event->getConfiguration());
        $this->events->shouldReceive('getByConfiguration')->once()->with($event->getConfiguration())->andReturn($event);
    }
}
