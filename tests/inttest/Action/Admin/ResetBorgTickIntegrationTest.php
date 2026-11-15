<?php

declare(strict_types=1);

namespace Stu\Action\Admin;

use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\ActionTestCase;
use Stu\Component\Borg\BorgSpherePhaseEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Admin\Action\ResetBorgTick;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\User;

final class ResetBorgTickIntegrationTest extends ActionTestCase
{
    /** @return iterable<string, array{BorgSpherePhaseEnum}> */
    public static function phases(): iterable
    {
        yield 'active cube' => [BorgSpherePhaseEnum::ROAMING];
        yield 'regenerating cube' => [BorgSpherePhaseEnum::REGENERATING];
    }

    #[DataProvider('phases')]
    public function testResetPersistsOnlyTheNewDeadlineAndPreservesAnOngoingAssimilation(BorgSpherePhaseEnum $phase): void
    {
        $container = $this->getContainer();
        $em = $container->get(EntityManagerInterface::class);
        $now = $container->get(StuTime::class)->time();
        $cube = $em->find(Ship::class, 77);
        $target = $em->find(Ship::class, 42);
        $cube->setUser($em->find(User::class, 10))->setLocation($target->getLocation())->setTractoredShip($target);
        $hull = $cube->getCondition()->getHull();
        $shield = $cube->getCondition()->getShield();
        $systems = $cube->getSystems()->map(static fn (SpacecraftSystem $system): array => [$system->getStatus(), $system->getMode()])->toArray();
        $quest = new NPCQuest()->setUser($cube->getUser())->setUserId(10)->setTitle('Borg-Aktionszeit')->setStart($now - 3600);
        $configuration = new BorgSphereConfiguration()->setId(2)->setColony($em->find(Colony::class, 42))->setQuest($quest)->setUser($cube->getUser())
            ->setBuildplan($em->find(SpacecraftBuildplan::class, 2324))->setEndTime($now + 86400)->setSettings(['tick_seconds' => 1800]);
        $event = new BorgSphereEvent()->setConfiguration($configuration)->setShipId(77)->setPhase($phase)->setNextTickAt($now - 10800)
            ->setFirstDisabledAt($now - 1800)->setTranswarpProgress(2300)->setTranswarpFloor(2000)->setInitialAttackDone(true);
        $entry = new SpacecraftAssimilated()->setSpacecraft($target)->start($cube, $now - 300, 900)->setProgress(3333);
        foreach ([$quest, $configuration, $event, $entry] as $entity) {
            $em->persist($entity);
        }
        $em->flush();
        $eventId = $event->getId();
        $information = new InformationWrapper();
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('setView')->once()->with(ShowScripts::VIEW_IDENTIFIER);
        $context->shouldReceive('getInfo')->andReturn($information);
        $clock = $this->mock(StuTime::class);
        $clock->shouldReceive('time')->once()->andReturn($now);
        $clock->shouldReceive('transformToStuDateTime')->once()->with($now + 1800)->andReturn('15.11.2396 14:30');
        $action = $container->make(ResetBorgTick::class, ['time' => $clock]);
        request::setMockVars(['borg_configuration_id' => 2]);

        $em->wrapInTransaction(fn () => $action->handle($context));
        $em->clear();

        $event = $em->find(BorgSphereEvent::class, $eventId);
        self::assertSame($now + 1800, $event->getNextTickAt());
        self::assertSame($phase, $event->getPhase());
        self::assertSame($now - 1800, $event->getFirstDisabledAt());
        self::assertSame(2300, $event->getTranswarpProgress());
        self::assertSame(2000, $event->getTranswarpFloor());
        self::assertTrue($event->isInitialAttackDone());
        self::assertSame(77, $event->getShipId());
        $entry = $em->find(SpacecraftAssimilated::class, 42);
        self::assertTrue($entry->isActive());
        self::assertSame($now - 300, $entry->getStartedAt());
        self::assertSame(900, $entry->getDurationSeconds());
        self::assertSame(3333, $entry->getProgress());
        self::assertSame(77, $entry->getSource()->getId());
        $cube = $em->find(Ship::class, 77);
        self::assertSame(42, $cube->getTractoredShip()->getId());
        self::assertSame($hull, $cube->getCondition()->getHull());
        self::assertSame($shield, $cube->getCondition()->getShield());
        self::assertSame($systems, $cube->getSystems()->map(static fn (SpacecraftSystem $system): array => [$system->getStatus(), $system->getMode()])->toArray());
        self::assertSame(['Nächste Borg-Aktion: 15.11.2396 14:30 Uhr'], $information->getInformations());
    }
}
