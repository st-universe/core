<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\ActionTestCase;
use Stu\Component\Borg\BorgSphereFlight;
use Stu\Component\Borg\BorgSphereNavigation;
use Stu\Component\Borg\BorgSpherePhaseEnum;
use Stu\Component\Borg\BorgSphereRaidService;
use Stu\Component\Borg\BorgSphereRaidTeam;
use Stu\Component\Borg\BorgSphereSpawner;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\Component\ControllerDiscoveryInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Message\Lib\DistributedMessageSenderInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\SkipDetectionInterface;
use Stu\Module\Spacecraft\Action\AttackSpacecraft\AttackSpacecraft;
use Stu\Module\Spacecraft\Action\CollectBorgNanites\CollectBorgNanites;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\View\ShowBorgSphere\ShowBorgSphere;
use Stu\Module\Tick\Process\BorgSphereTick;
use Stu\Module\Twig\TwigHelper;
use Stu\Orm\Entity\BorgSphereConfiguration;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\BorgSphereRaid;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;
use Twig\Environment;

final class BorgSphereIntegrationTest extends ActionTestCase
{
    private EntityManagerInterface $em;
    private BorgSphereRepositoryInterface $events;
    private BorgSphereConfiguration $configuration;
    private BorgSphereEvent $event;
    private Ship $source;
    private Ship $sphere;
    private int $now;

    public function testEventSphereDoesNotAutomaticallyAttackIncomingPlayers(): void
    {
        $this->event->setPhase(BorgSpherePhaseEnum::ROAMING);
        $this->sphere->getCondition()->setDisabled(false);
        $this->em->flush();
        $wrapper = $this->getContainer()->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->sphere);
        self::assertTrue($this->getContainer()->get(SkipDetectionInterface::class)->isSkipped($this->source, $wrapper, null, new ArrayCollection()));
    }

    public function testBorgPopupsAndActionsUseSharedControllersForShipsAndStations(): void
    {
        $container = $this->getContainer();
        $discovery = $container->get(ControllerDiscoveryInterface::class);
        $sharedActions = $container->get('SPACECRAFT_ACTIONS');
        $sharedViews = $container->get('SPACECRAFT_VIEWS');
        foreach ([ModuleEnum::SHIP, ModuleEnum::STATION] as $module) {
            $actions = $discovery->getControllers($module, false);
            $views = $discovery->getControllers($module, true);
            foreach ([AttackSpacecraft::ACTION_IDENTIFIER, CollectBorgNanites::ACTION_IDENTIFIER] as $identifier) {
                self::assertSame($sharedActions[$identifier], $actions[$identifier], $module->name . ': ' . $identifier);
            }
            self::assertSame($sharedViews[ShowBorgSphere::VIEW_IDENTIFIER], $views[ShowBorgSphere::VIEW_IDENTIFIER], $module->name);
        }
    }

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $container = $this->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->events = $container->get(BorgSphereRepositoryInterface::class);
        $this->now = $container->get(StuTime::class)->time();
        $this->source = $this->em->find(Ship::class, 42);
        $this->sphere = $this->em->find(Ship::class, 77);
        for ($i = 0; $i < 5; $i++) {
            $assignment = $container->get(CrewCreatorInterface::class)->create($this->source->getUser()->getId())->setSpacecraft($this->source)->setSlot(CrewTypeEnum::CREWMAN);
            $this->source->getCrewAssignments()->add($assignment);
            $this->em->persist($assignment);
        }
        $colony = $this->em->find(Colony::class, 42);
        $this->source->setLocation($colony->getStarsystemMap());
        $this->sphere->setLocation($colony->getStarsystemMap())->setUser($this->em->find(User::class, 10));
        foreach ($this->source->getSystems() as $system) {
            if (in_array($system->getSystemType(), [SpacecraftSystemTypeEnum::WARPDRIVE, SpacecraftSystemTypeEnum::SHIELDS, SpacecraftSystemTypeEnum::CLOAK], true)) {
                $system->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            }
        }
        $quest = new NPCQuest()->setUser($this->em->find(User::class, 10))->setUserId(10)->setTitle('Sphärentest')->setStart($this->now - 100);
        $this->em->persist($quest);
        $this->configuration = new BorgSphereConfiguration()->setId(1)->setColony($colony)->setQuest($quest)->setUser($this->em->find(User::class, 10))->setBuildplan($this->em->find(SpacecraftBuildplan::class, 2324))->setEndTime($this->now + 604800)->setSettings($this->settings());
        $this->event = new BorgSphereEvent()->setConfiguration($this->configuration)->setShipId($this->sphere->getId())->setPhase(BorgSpherePhaseEnum::REGENERATING)->setFirstDisabledAt($this->now)->setTranswarpProgress(1000)->setNextTickAt($this->now + 3600);
        $this->sphere->getCondition()->setDisabled(true);
        $this->em->persist($this->configuration);
        $this->em->persist($this->event);
        $this->em->flush();
    }

    public function testRaidAddsNanitesAndPersistsAccountCooldownAcrossShips(): void
    {
        $service = $this->raidService(success: true);
        $before = $this->source->getStorage()->get(2)?->getAmount() ?? 0;
        $this->collect($service);
        self::assertSame($before + 5, $this->source->getStorage()->get(2)->getAmount());
        $raid = $this->events->getRaid($this->source->getUser());
        self::assertSame($this->now, $raid->getLastAttemptAt());
        self::assertSame(1, $raid->getAttempts());
        $other = $this->em->find(Ship::class, 78);
        $other->setLocation($this->sphere->getLocation());
        $information = $this->collect($service, $other);
        self::assertStringContainsString('Ein weiteres Außenteam kann ab', $information->getInformations()[0]);
        self::assertSame(1, $raid->getAttempts());
    }

    public function testFailedRaidPermanentlyRemovesCrewAndAlsoStartsCooldown(): void
    {
        $captain = $this->source->getCrewAssignments()->first();
        $captain->setSlot(CrewTypeEnum::CAPTAIN);
        $before = $this->source->getCrewCount();
        $crewCount = $this->em->getRepository(Crew::class)->count([]);
        $information = $this->collect($this->raidService(success: false));
        self::assertSame($before - 2, $this->source->getCrewCount());
        self::assertSame($crewCount - 2, $this->em->getRepository(Crew::class)->count([]));
        self::assertTrue($this->source->getCrewAssignments()->contains($captain));
        self::assertStringContainsString('2 Crewmitglieder', $information->getInformations()[0]);
        self::assertStringContainsString('assimiliert', $information->getInformations()[0]);
        self::assertSame($this->now, $this->events->getRaid($this->source->getUser())->getLastAttemptAt());
    }

    public function testFullStorageRejectsRaidWithoutCooldownOrCrewLoss(): void
    {
        $remaining = $this->source->getMaxStorage() - $this->source->getStorageSum();
        $this->getContainer()->get(StorageManagerInterface::class)->upperStorage($this->source, $this->em->find(Commodity::class, 1), $remaining);
        $this->em->flush();
        $before = $this->source->getCrewCount();
        $information = $this->collect($this->raidService(success: true, attempts: 0));
        self::assertStringContainsString('5 freie Lagerplätze', $information->getInformations()[0]);
        self::assertNull($this->events->getRaid($this->source->getUser()));
        self::assertSame($before, $this->source->getCrewCount());
    }

    public function testCooldownExpiresAfterExactlyTwoHours(): void
    {
        $service = $this->raidService(success: true, attempts: 2);
        $this->collect($service);
        $raid = $this->events->getRaid($this->source->getUser());
        $raid->setLastAttemptAt($this->now - 7200);
        $this->em->flush();
        $this->collect($service);
        self::assertSame(2, $raid->getAttempts());
    }

    public function testCompletedEventCannotBeFarmed(): void
    {
        $this->configuration->setEndTime($this->now);
        $information = $this->collect($this->raidService(success: true, attempts: 0));
        self::assertStringContainsString('nicht mehr für eine Außenmission verfügbar', $information->getInformations()[0]);
        self::assertNull($this->events->getRaid($this->source->getUser()));
    }

    public function testSpawnIsAwayFromColonyUsesInstanceStatsAndCannotBeRepeated(): void
    {
        $this->events->deleteConfiguration($this->configuration);
        $this->em->flush();
        $this->em->clear();
        $this->configuration = new BorgSphereConfiguration()->setId(2)->setColony($this->em->find(Colony::class, 42))->setQuest($this->em->find(NPCQuest::class, $this->configuration->getQuest()->getId()))->setUser($this->em->find(User::class, 10))->setBuildplan($this->em->find(SpacecraftBuildplan::class, 2324))->setEndTime($this->now + 604800)->setSettings($this->settings());
        $this->em->persist($this->configuration);
        $this->em->flush();
        $spawner = $this->getContainer()->get(BorgSphereSpawner::class);
        $event = $this->em->wrapInTransaction(fn () => $spawner->spawn(2, new InformationWrapper()));
        self::assertNotNull($event);
        $sphere = $this->em->find(Ship::class, $event->getShipId());
        self::assertNotSame($this->configuration->getColony()->getStarsystemMap(), $sphere->getLocation());
        self::assertSame($this->configuration->getColony()->getSystem(), $sphere->getSystem());
        self::assertSame($this->source->getMaxHull(), $sphere->getMaxHull());
        self::assertSame($this->source->getMaxShield(true), $sphere->getMaxShield(true));
        self::assertSame($sphere->getMaxShield(true), $sphere->getCondition()->getShield());
        self::assertTrue($sphere->isShielded());
        self::assertTrue($sphere->hasActiveWeapon());
        self::assertTrue($sphere->getNbs());
        self::assertSame(SpacecraftAlertStateEnum::ALERT_YELLOW, $this->getContainer()->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($sphere)->getAlertState());
        self::assertNull($this->em->wrapInTransaction(fn () => $spawner->spawn(2, new InformationWrapper())));
    }

    public function testNavigationRefusesRedAlertAndApproachesViaSafeFields(): void
    {
        $report = [];
        $messages = $this->mock(DistributedMessageSenderInterface::class);
        $messages->shouldReceive('distributeMessageCollection')->andReturnUsing(function (MessageCollectionInterface $collection) use (&$report): void {
            $report = $collection->getInformationDump()->getInformations();
        });
        $flight = $this->getContainer()->make(BorgSphereFlight::class, ['messages' => $messages]);
        $navigation = $this->getContainer()->make(BorgSphereNavigation::class, ['flight' => $flight]);
        $spawn = $navigation->spawnLocation($this->configuration);
        self::assertNotNull($spawn);
        $this->source->getLocation()->getSpacecrafts()->removeElement($this->source);
        $this->source->getFleet()?->getShips()->toArray();
        $this->source->setLocation($spawn)->setDockedTo(null)->setFleet(null);
        $spawn->getSpacecrafts()->set($this->source->getId(), $this->source);
        $wrapper = $this->getContainer()->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->sphere);
        $wrapper->setAlertState(SpacecraftAlertStateEnum::ALERT_RED);
        self::assertFalse($navigation->isSafe($this->sphere->getLocation()));
        $this->em->flush();
        self::assertNull($this->source->getFleet());
        $navigation->approach($this->source, $this->configuration);
        self::assertNotSame($spawn, $this->source->getLocation(), implode(' ', $report));
        self::assertNotSame($this->sphere->getLocation(), $this->source->getLocation());
        self::assertInstanceOf(StarSystemMap::class, $this->source->getLocation());
        self::assertTrue($navigation->isSafe($this->source->getLocation()));
        self::assertFalse($this->source->getWarpDriveState());
    }

    public function testSpawnSkipsEvenTheFarthestSectorWhenRedAlertShipsArePresent(): void
    {
        $navigation = $this->getContainer()->get(BorgSphereNavigation::class);
        $first = $navigation->spawnLocation($this->configuration);
        self::assertNotNull($first);
        $this->source->getLocation()->getSpacecrafts()->removeElement($this->source);
        $this->source->setLocation($first);
        $first->getSpacecrafts()->set($this->source->getId(), $this->source);
        $this->getContainer()->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->source)->setAlertState(SpacecraftAlertStateEnum::ALERT_RED);
        $this->em->flush();
        $safe = $navigation->spawnLocation($this->configuration);
        self::assertNotNull($safe);
        self::assertNotSame($first, $safe);
        self::assertTrue($navigation->isSafe($safe));
    }

    public function testDeletingQuestRetiresSphereAndRemovesEventConfiguration(): void
    {
        $id = $this->sphere->getId();
        $this->em->remove($this->configuration->getQuest());
        $this->em->flush();
        self::assertNull($this->em->find(Ship::class, $id));
        self::assertSame(0, $this->em->getRepository(BorgSphereConfiguration::class)->count([]));
        self::assertSame(0, $this->em->getRepository(BorgSphereEvent::class)->count([]));
    }

    public function testExpiredSphereRetreatsInsteadOfRemainingAsFarm(): void
    {
        $id = $this->sphere->getId();
        $this->configuration->setEndTime($this->now);
        $this->event->setNextTickAt($this->now);
        $this->em->flush();
        $this->em->wrapInTransaction(fn () => $this->getContainer()->get(BorgSphereTick::class)->work());
        self::assertSame(BorgSpherePhaseEnum::ESCAPED, $this->event->getPhase());
        self::assertNull($this->configuration->getQuest()->getEnd());
        self::assertNull($this->event->getShipId());
        self::assertNull($this->em->find(Ship::class, $id));
    }

    public function testPopupRendersWorkingControlsAndThenAccountCooldown(): void
    {
        $this->sphere->setName('[b]Test-Sphäre[/b]');
        $html = $this->renderPopup();
        self::assertStringContainsString('<th id="popup-drag"><strong>Test-Sphäre</strong></th>', $html);
        self::assertSame(1, substr_count($html, 'Test-Sphäre'));
        self::assertStringNotContainsString('Das Außenteam umfasst', $html);
        self::assertStringContainsString('<tr><th>Möglicher Gewinn</th><td>5 Naniten</td></tr>', $html);
        self::assertStringContainsString('Außenteam senden', $html);
        self::assertStringContainsString('class="button"', $html);
        self::assertStringContainsString('Transwarpantrieb', $html);
        self::assertStringNotContainsString('Transwarpantrieb beschießen', $html);
        $this->event->setPhase(BorgSpherePhaseEnum::ROAMING);
        $this->sphere->getCondition()->setDisabled(false);
        $html = $this->renderPopup();
        self::assertStringContainsString('Transwarpantrieb beschießen', $html);
        $this->events->saveRaid(new BorgSphereRaid()->setUser($this->source->getUser())->setLastAttemptAt($this->now)->setAttempts(1));
        $this->em->flush();
        $html = $this->renderPopup();
        self::assertStringNotContainsString('Außenteam senden', $html);
        self::assertStringContainsString('Nächste Außenmission ab', $html);
        self::assertStringContainsString('Uhr', $html);
    }

    /** @return iterable<string, array{int}> */
    public static function missionSurplus(): iterable
    {
        yield 'buildplan minimum' => [0];
        yield 'minimum plus two' => [2];
        yield 'minimum plus five' => [5];
    }

    #[DataProvider('missionSurplus')]
    public function testPopupAndMissionUseOnlyCrewAboveTheBuildplanMinimum(int $surplus): void
    {
        $minimum = $this->source->getCrewCount() - $surplus;
        $this->source->getBuildplan()->setCrew($minimum);
        $service = $this->raidService(success: false, attempts: $surplus === 0 ? 0 : 1);
        self::assertSame($surplus, $service->getTeamSize($this->source, $this->event));
        $this->em->flush();
        $html = $this->renderPopup();
        if ($surplus === 0) {
            self::assertStringNotContainsString('Außenteam senden', $html);
            $information = $this->collect($service);
            self::assertCount(1, $information->getInformations());
            self::assertStringContainsString('Mindestbesatzung des Bauplans (' . $minimum . ')', $information->getInformations()[0]);
            self::assertNull($this->events->getRaid($this->source->getUser()));
        } else {
            self::assertStringContainsString('Außenteam senden', $html);
            $this->collect($service);
        }
        self::assertGreaterThanOrEqual($minimum, $this->source->getCrewCount());
    }

    public function testTranswarpEscapeLeavesTheQuestOpen(): void
    {
        $this->event->setTranswarpProgress(7900)->setNextTickAt($this->now);
        $this->em->flush();
        $this->em->wrapInTransaction(fn () => $this->getContainer()->get(BorgSphereTick::class)->work());
        self::assertSame(BorgSpherePhaseEnum::ESCAPED, $this->event->getPhase());
        self::assertNull($this->configuration->getQuest()->getEnd());
    }

    private function renderPopup(): string
    {
        $variables = ['SESSIONSTRING' => 'session', 'INFORMATION' => [], 'ACHIEVEMENTS' => [], 'EXECUTEJSAFTERRENDER' => [], 'JAVASCRIPTPATH' => '/static', 'MACRO' => 'html/ship/borgSphere.twig'];
        $game = $this->mock(ViewControllerContext::class);
        $game->shouldReceive('getUser')->andReturn($this->source->getUser());
        $game->shouldReceive('getInfo')->andReturn(new InformationWrapper());
        $game->shouldReceive('setPageTitle')->once()->with($this->sphere->getName())->andReturnUsing(static function (string $title) use (&$variables): void {
            $variables['PAGETITLE'] = $title;
        });
        $game->shouldReceive('setMacroInAjaxWindow')->with('')->once();
        $game->shouldReceive('setMacroInAjaxWindow')->with('html/ship/borgSphere.twig')->once();
        $game->shouldReceive('setTemplateVar')->andReturnUsing(function (string $key, mixed $value) use (&$variables): void {
            $variables[$key] = $value;
        });
        request::setMockVars(['id' => $this->source->getId(), 'target' => $this->sphere->getId()]);
        try {
            $this->em->wrapInTransaction(fn () => $this->getContainer()->get(ShowBorgSphere::class)->handle($game));
        } finally {
            request::setMockVars(null);
        }
        $twig = new Environment($this->getContainer()->get(Environment::class)->getLoader(), ['cache' => false, 'strict_variables' => true]);
        $this->getContainer()->make(TwigHelper::class, ['environment' => $twig])->registerFiltersAndFunctions();
        return $twig->render('html/ajaxwindow.twig', $variables);
    }

    private function raidService(bool $success, int $attempts = 1): BorgSphereRaidService
    {
        $random = $this->mock(StuRandom::class);
        $messages = $this->mock(PrivateMessageSenderInterface::class);
        $messages->shouldReceive('send')->times($attempts);
        if ($attempts !== 0) {
            $random->shouldReceive('rand')->with(1, 100)->times($attempts)->andReturn($success ? 1 : 100);
            $random->shouldReceive('rand')->with($success ? 3 : 1, $success ? 5 : 3)->times($attempts)->andReturn($success ? 5 : 2);
            if (!$success) {
                $random->shouldReceive('array_rand')->twice()->andReturnUsing(static fn (array $crew): int => array_key_first($crew));
            }
        }
        $team = $this->getContainer()->make(BorgSphereRaidTeam::class, ['random' => $random]);
        return $this->getContainer()->make(BorgSphereRaidService::class, ['random' => $random, 'messages' => $messages, 'team' => $team]);
    }

    private function collect(BorgSphereRaidService $service, ?Ship $source = null): InformationWrapper
    {
        $information = new InformationWrapper();
        $wrapper = $this->getContainer()->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($source ?? $this->source);
        $this->em->wrapInTransaction(fn () => $service->collect($wrapper, $this->sphere, $information));
        return $information;
    }

    /** @return array<string, int> */
    private function settings(): array
    {
        return ['tick_seconds' => 3600, 'normal_regen_percent' => 2, 'transwarp_regen_bp' => 100, 'transwarp_escape_bp' => 8000, 'transwarp_initial_bp' => 1000, 'transwarp_floor_step_bp' => 1000, 'wake_percent' => 80, 'raid_cooldown_seconds' => 7200, 'raid_reward_min' => 3, 'raid_reward_max' => 5, 'raid_success_disabled' => 75, 'raid_success_active' => 25, 'raid_team_size' => 5, 'raid_loss_min' => 1, 'raid_loss_max' => 3, 'nanite_commodity_id' => 2, 'roam_radius' => 3];
    }
}
