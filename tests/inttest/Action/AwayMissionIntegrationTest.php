<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use request;
use Stu\ActionTestCase;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Quest\AwayMission\AwayMissionAccess;
use Stu\Component\Quest\AwayMission\AwayMissionDeletionHandler;
use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;
use Stu\Component\Quest\AwayMission\AwayMissionService;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Spacecraft\Action\AwayMission\AwayMission as AwayMissionAction;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\View\ShowAwayMission\ShowAwayMission;
use Stu\Module\Spacecraft\View\ShowColonyScan\ShowColonyScan;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Twig\TwigHelper;
use Stu\Orm\Entity\AwayMission;
use Stu\Orm\Entity\AwayMissionDialogue;
use Stu\Orm\Entity\AwayMissionField;
use Stu\Orm\Entity\AwayMissionProgress;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyProtection;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapRegionSettlement;
use Stu\Orm\Entity\NPCQuest;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AwayMissionRepositoryInterface;
use Stu\Orm\Repository\ColonyProtectionRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\NPCQuestUserRepositoryInterface;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;
use Twig\Environment;

final class AwayMissionIntegrationTest extends ActionTestCase
{
    private EntityManagerInterface $em;
    private AwayMissionRepositoryInterface $missions;
    private AwayMissionService $service;
    private AwayMission $mission;
    private User $user;
    private Ship $ship;
    private ?string $previousMethod;

    /** @var list<string> */
    private array $views = [];

    #[\Override]
    public function setUp(): void
    {
        $this->previousMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        parent::setUp();
        $container = $this->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->missions = $container->get(AwayMissionRepositoryInterface::class);
        $this->service = $container->get(AwayMissionService::class);
        $this->user = $this->em->find(User::class, 101);
        $this->ship = $this->em->find(Ship::class, 42);
        $colony = $this->em->find(Colony::class, 42);
        $this->ship->setLocation($colony->getStarsystemMap());
        $matrix = new SpacecraftSystem()->setSpacecraft($this->ship)->setSystemType(SpacecraftSystemTypeEnum::MATRIX_SCANNER)->setStatus(100);
        $this->em->persist($matrix);
        $this->ship->getSystems()->set(SpacecraftSystemTypeEnum::MATRIX_SCANNER->value, $matrix);
        $slot = 1;
        foreach ($this->ship->getCrewAssignments() as $assignment) {
            $assignment->setSlot($slot <= 6 ? CrewTypeEnum::from($slot) : null);
            $slot++;
        }
        $quest = new NPCQuest()->setUser($this->user)->setUserId(101)->setTitle('Außenmission')->setStart(time() - 100);
        $this->em->persist($quest);
        $this->mission = new AwayMission()->setColony($colony)->setQuest($quest)->setTitle('Spurensuche')
            ->setRewardCommodity($this->em->find(Commodity::class, 2))->setRewardAmount(5);
        $this->em->persist($this->mission);
        foreach ([23, 24] as $field) {
            $this->em->persist(new AwayMissionField()->setMission($this->mission)->setFieldId($field));
        }
        $this->em->persist(new AwayMissionDialogue()->setMission($this->mission)->setPage(10)->setPosition(10)->setText('Eine fremde Oberfläche'));
        $this->em->persist(new AwayMissionDialogue()->setMission($this->mission)->setPage(10)->setPosition(20)
            ->setSpeaker(CrewTypeEnum::CAPTAIN)->setText('{crew.4.rank} {crew.4.name}, melden Sie sich auf der {ship.name}'));
        $this->em->persist(new AwayMissionDialogue()->setMission($this->mission)->setPage(15)->setPosition(10)->setShowSurface(true));
        $this->em->persist(new AwayMissionDialogue()->setMission($this->mission)->setPage(20)->setPosition(10)
            ->setSpeaker(CrewTypeEnum::SCIENCE)->setText('Mission abgeschlossen'));
        $metadata = $this->em->getClassMetadata(Commodity::class);
        $generatorType = $metadata->generatorType;
        $generator = $metadata->idGenerator;
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());
        try {
            foreach (AwayMissionResearchEnum::cases() as $system) {
                $commodity = new Commodity()->setName('Datensatz ' . $system->getDescription());
                $metadata->setFieldValue($commodity, 'id', $system->getCommodityId());
                $this->em->persist($commodity);
            }
            $this->em->flush();
        } finally {
            $metadata->setIdGeneratorType($generatorType);
            $metadata->setIdGenerator($generator);
        }
    }

    #[\Override]
    public function tearDown(): void
    {
        request::setMockVars(null);
        if ($this->previousMethod === null) {
            unset($_SERVER['REQUEST_METHOD']);
        } else {
            $_SERVER['REQUEST_METHOD'] = $this->previousMethod;
        }
        parent::tearDown();
    }

    public function testInitialSurfaceScanIsRequiredAndQuestMembershipIsNotRequired(): void
    {
        self::assertNull($this->getContainer()->get(NPCQuestUserRepositoryInterface::class)->getByQuestAndUser($this->mission->getQuest()->getId(), 101));
        self::assertTrue($this->service->isAvailable($this->mission));
        $information = $this->dispatch('scan', field: 23);
        self::assertContains('Scanne zuerst die Oberfläche', $information->getInformations());
        self::assertNull($this->missions->getProgress($this->mission, $this->user));
    }

    public function testNormalMatrixScanOpensPlayerDialogueAndInitializesProgress(): void
    {
        ['macro' => $macro, 'variables' => $variables] = $this->openSurfaceScan();
        self::assertSame('html/ship/awayMission.twig', $macro);
        self::assertNotNull($this->missions->getProgress($this->mission, $this->user));
        self::assertTrue($variables['MISSION_DIALOGUE']);
        self::assertSame(1, $variables['MISSION_PAGE']);
        self::assertArrayHasKey('MISSION_ENTRIES', $variables);
        self::assertArrayNotHasKey('MISSION_VARIANTS', $variables);
    }

    public function testActiveMissionCanBeOpenedAndScannedWithoutMatrixScanner(): void
    {
        $this->removeMatrixScanner();
        $result = $this->openSurfaceScan();
        self::assertSame('html/ship/awayMission.twig', $result['macro']);
        self::assertTrue($result['variables']['MISSION_DIALOGUE']);
        $this->dispatch('page', 2);
        $this->dispatch('scan', field: 23);
        self::assertSame([23], $this->missions->getProgress($this->mission, $this->user)->getScannedFields());
    }

    public function testUnrelatedColonyStillRequiresMatrixScanner(): void
    {
        $this->removeMatrixScanner();
        $this->getContainer()->get(AwayMissionDeletionHandler::class)->deleteByQuest($this->mission->getQuest());
        $this->em->flush();
        $this->expectException(SanityCheckException::class);
        $this->expectExceptionMessage('matrix scanner is not healthy');
        $this->openSurfaceScan();
    }

    /** @return iterable<string, array{bool}> */
    public static function inactiveMissionStates(): iterable
    {
        yield 'not started' => [false];
        yield 'completed' => [true];
    }

    #[DataProvider('inactiveMissionStates')]
    public function testInactiveMissionStillRequiresMatrixScanner(bool $completed): void
    {
        $this->removeMatrixScanner();
        $quest = $this->mission->getQuest();
        if ($completed) {
            $quest->setEnd(time());
        } else {
            $quest->setStart(time() + 3600);
        }
        $this->em->flush();
        $this->expectException(SanityCheckException::class);
        $this->expectExceptionMessage('matrix scanner is not healthy');
        $this->openSurfaceScan();
    }

    public function testScanButtonWithoutMatrixScannerIsOnlyVisibleForActiveMission(): void
    {
        $this->removeMatrixScanner();
        $this->mission->getColony()->setUser($this->em->find(User::class, 1));
        $this->em->flush();
        self::assertStringContainsString('onclick="showColonyScan(this);"', $this->renderScanControls());
        $this->mission->getQuest()->setStart(time() + 3600);
        self::assertStringNotContainsString('onclick="showColonyScan(this);"', $this->renderScanControls());
        $this->mission->getQuest()->setStart(time() - 1)->setEnd(time());
        self::assertStringNotContainsString('onclick="showColonyScan(this);"', $this->renderScanControls());
    }

    public function testScanCountsEachConfiguredFieldOnceAndDoesNotChangeEnergy(): void
    {
        $progress = $this->initializeProgress();
        $energy = $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::EPS)->getData();
        $this->dispatch('page', 2);
        $this->dispatch('scan', field: 23);
        $this->dispatch('scan', field: 23);
        $this->dispatch('scan', field: 25);
        self::assertSame([23], $progress->getScannedFields());
        self::assertSame(1, $this->service->getScannedFieldCount($this->mission, $progress));
        self::assertFalse($this->service->isScanComplete($this->mission, $progress));
        $this->dispatch('scan', field: 24);
        self::assertTrue($this->service->isScanComplete($this->mission, $progress));
        self::assertSame($energy, $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::EPS)->getData());
    }

    public function testMissingOfficersPreventStartAndAreReported(): void
    {
        $progress = $this->scanAllFields();
        foreach ($this->ship->getCrewAssignments() as $assignment) {
            if (in_array($assignment->getSlot(), [CrewTypeEnum::SCIENCE, CrewTypeEnum::TECHNICAL], true)) {
                $assignment->setSlot(null);
            }
        }
        $this->em->flush();
        $information = $this->dispatch('page', 3);
        self::assertSame(['Für die Außenmission fehlen: Wissenschaftler, Ingenieur'], $information->getInformations());
    }

    public function testNavigatorIsRequiredButCrewmenAreOptional(): void
    {
        $progress = $this->initializeProgress();
        $navigator = $this->service->getCrew($this->ship)[CrewTypeEnum::NAVIGATION->value];
        $navigator->setSlot(null);
        $this->em->flush();
        self::assertSame(['Für die Außenmission fehlen: Navigator'], $this->dispatch('page', 2)->getInformations());
        self::assertSame(1, $progress->getLastPage());
        $navigator->setSlot(CrewTypeEnum::NAVIGATION);
        foreach ($this->ship->getCrewAssignments()->toArray() as $assignment) {
            if ($assignment->getSlot() === null || $assignment->getSlot() === CrewTypeEnum::CREWMAN) {
                $this->ship->getCrewAssignments()->removeElement($assignment);
            }
        }
        self::assertTrue($this->service->hasRequiredCrew($this->ship));
    }

    public function testScanIsAnUnskippableStepBetweenDialoguePages(): void
    {
        $progress = $this->initializeProgress();
        $this->dispatch('scan', field: 23);
        self::assertSame([], $progress->getScannedFields());
        self::assertTrue($this->getPopupVariables($progress)['MISSION_DIALOGUE']);
        $this->dispatch('page', 2);
        $variables = $this->getPopupVariables($progress);
        self::assertFalse($variables['MISSION_DIALOGUE']);
        self::assertFalse($variables['MISSION_CAN_CONTINUE']);
        $this->dispatch('page', 3);
        self::assertSame(2, $progress->getLastPage());
        $this->dispatch('scan', field: 23);
        $this->dispatch('scan', field: 24);
        self::assertTrue($this->getPopupVariables($progress)['MISSION_CAN_CONTINUE']);
        $this->dispatch('page', 3);
        self::assertTrue($this->getPopupVariables($progress)['MISSION_DIALOGUE']);
    }

    public function testRandomFindingsPersistWhenTheMissionIsReopened(): void
    {
        $progress = $this->initializeProgress();
        $research = $progress->getResearchSystem();
        $contact = $progress->getContactFaction();
        $crewId = $progress->getReportingCrewId();
        self::assertNotNull($research);
        self::assertNotNull($contact);
        self::assertTrue($contact->getChooseable());
        self::assertNotSame($this->user->getFactionId(), $contact->getId());
        $this->em->refresh($progress);
        $this->service->prepareProgress($progress, $this->ship);
        self::assertSame($research, $progress->getResearchSystem());
        self::assertSame($contact->getId(), $progress->getContactFaction()->getId());
        self::assertSame($crewId, $progress->getReportingCrewId());
        $text = $this->service->replaceVariables('{research.system}: {research.finding}. Kontakt: {contact.signature}', $this->ship, $progress);
        self::assertStringContainsString($research->getDescription(), $text);
        self::assertStringContainsString($research->getFinding(), $text);
        self::assertStringNotContainsString('{', $text);
    }

    public function testShipReportUsesRandomCrewmanOrFallsBackToNarration(): void
    {
        $progress = $this->initializeProgress();
        self::assertNotNull($progress->getReportingCrewId());
        $entry = new AwayMissionDialogue()->setMission($this->mission)->setPage(20)->setPosition(20)
            ->setSpeaker(CrewTypeEnum::CREWMAN)->setText('Wir erfassen {contact.signature}');
        $this->em->persist($entry);
        $this->em->flush();
        $rendered = $this->service->renderPage($this->mission, $this->ship, 3, $progress);
        self::assertNotNull($rendered[1]['speaker']);
        foreach ($this->ship->getCrewAssignments()->toArray() as $assignment) {
            if ($assignment->getSlot() === null || $assignment->getSlot() === CrewTypeEnum::CREWMAN) {
                $this->ship->getCrewAssignments()->removeElement($assignment);
            }
        }
        $rendered = $this->service->renderPage($this->mission, $this->ship, 3, $progress);
        self::assertNull($rendered[1]['speaker']);
        self::assertStringContainsString('ist eine Stimme zu hören', $rendered[1]['text']);
    }

    public function testPagesCannotBeSkippedAndRewardRequiresSpaceAndCanOnlyBeClaimedOnce(): void
    {
        $progress = $this->scanAllFields();
        self::assertTrue($progress->isStarted());
        $this->dispatch('page', 99);
        self::assertSame(2, $progress->getLastPage());
        self::assertContains('Schließe zuerst die Außenmission ab', $this->dispatch('reward')->getInformations());
        $this->dispatch('page', 3);
        self::assertSame(3, $progress->getLastPage());
        $this->dispatch('page', 1);
        self::assertSame(3, $progress->getLastPage());
        $this->mission->setRewardAmount($this->ship->getMaxStorage() - $this->ship->getStorageSum());
        $this->em->flush();
        $before = $this->ship->getStorageSum();
        self::assertStringContainsString('freie Lagereinheiten', $this->dispatch('reward')->getInformations()[0]);
        self::assertSame([ShowAwayMission::VIEW_IDENTIFIER], $this->views);
        self::assertFalse($progress->isRewardReceived());
        self::assertSame($before, $this->ship->getStorageSum());
        $this->mission->setRewardAmount(5);
        $this->em->flush();
        $information = $this->dispatch('reward');
        self::assertTrue($progress->isRewardReceived());
        self::assertSame($before + 6, $this->ship->getStorageSum());
        self::assertSame([ShowAwayMission::VIEW_IDENTIFIER, ShowSpacecraft::VIEW_IDENTIFIER], $this->views);
        self::assertSame([
            '5 ' . $this->mission->getRewardCommodity()->getName() . ' wurden dem Lagerraum hinzugefügt',
            '1 Datensatz ' . $progress->getResearchSystem()->getDescription() . ' wurde dem Lagerraum hinzugefügt'
        ], $information->getInformations());
        self::assertSame(1, $this->ship->getStorage()->get($progress->getResearchSystem()->getCommodityId())->getAmount());
        $this->dispatch('reward');
        self::assertSame($before + 6, $this->ship->getStorageSum());
        self::assertSame([ShowAwayMission::VIEW_IDENTIFIER, ShowSpacecraft::VIEW_IDENTIFIER], $this->views);
    }

    public function testReturnToSurfaceKeepsProgressAndDoesNotConsumeEnergy(): void
    {
        $progress = $this->scanAllFields();
        $energy = $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::EPS)->getData();
        $variables = $this->getPopupVariables($progress, ['mission_page' => 2]);
        self::assertFalse($variables['MISSION_DIALOGUE']);
        self::assertSame(2, $variables['SCAN_COUNT']);
        self::assertArrayHasKey('SURFACE', $variables);
        self::assertTrue($progress->isStarted());
        self::assertSame([23, 24], $progress->getScannedFields());
        $this->dispatch('scan', field: 23);
        self::assertFalse($this->getPopupVariables($progress, ['mission_page' => 2, 'mission_action' => 'scan'])['MISSION_DIALOGUE']);
        self::assertSame($energy, $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::EPS)->getData());
        self::assertTrue($this->getPopupVariables($progress, ['mission_page' => 1])['MISSION_DIALOGUE']);
    }

    public function testProgressIsIndependentForEachPlayer(): void
    {
        $first = $this->scanAllFields();
        $other = $this->em->find(User::class, 102);
        $second = $this->em->wrapInTransaction(fn (): AwayMissionProgress => $this->missions->initializeProgress($this->mission, $other));
        self::assertNotSame($first->getId(), $second->getId());
        self::assertSame([], $second->getScannedFields());
        self::assertFalse($second->isRewardReceived());
    }

    public function testFactionTextOverridesDefaultAndDialogueUsesCurrentCrew(): void
    {
        $faction = $this->em->find(Faction::class, $this->user->getFactionId());
        $this->em->persist(new AwayMissionDialogue()->setMission($this->mission)->setFaction($faction)
            ->setPage(10)->setPosition(10)->setText('Fraktionsvariante'));
        $this->em->flush();
        $pages = $this->service->getPages($this->mission, $this->user->getFactionId());
        self::assertSame('Fraktionsvariante', $pages[0][0]->getText());
        self::assertSame(3, count($pages));
        $science = $this->service->getCrew($this->ship)[CrewTypeEnum::SCIENCE->value]->getCrew();
        $rank = $this->getContainer()->get(UserCrewRankRepositoryInterface::class)->getRankName($science->getUser(), $science->getRank());
        $rendered = $this->service->renderPage($this->mission, $this->ship, 1, $this->initializeProgress());
        self::assertNull($rendered[0]['speaker']);
        self::assertSame($rank . ' ' . $science->getName() . ', melden Sie sich auf der ' . $this->ship->getName(), $rendered[1]['text']);
        self::assertNotNull($rendered[1]['speaker']);
        self::assertNotNull($rendered[1]['image']);
        self::assertFalse($rendered[1]['right']);
    }

    public function testPlayerFactionCannotBeOverriddenByTheRequest(): void
    {
        $progress = $this->initializeProgress();
        $foreignFactionId = null;
        foreach ($this->getContainer()->get(FactionRepositoryInterface::class)->getByChooseable(true) as $faction) {
            $this->em->persist(new AwayMissionDialogue()->setMission($this->mission)->setFaction($faction)
                ->setPage(10)->setPosition(10)->setText('Variante ' . $faction->getId()));
            if ($faction->getId() !== $this->user->getFactionId()) {
                $foreignFactionId = $faction->getId();
                $this->em->persist(new AwayMissionDialogue()->setMission($this->mission)->setFaction($faction)
                    ->setPage(10)->setPosition(30)->setShowSurface(true));
            }
        }
        $this->em->flush();
        self::assertNotNull($foreignFactionId);
        $variables = $this->getPopupVariables($progress, ['mission_faction' => $foreignFactionId]);
        self::assertSame('Variante ' . $this->user->getFactionId(), $variables['MISSION_ENTRIES'][0]['text']);
        self::assertTrue($variables['MISSION_DIALOGUE']);
        $html = $this->renderPopupHtml($variables);
        self::assertStringNotContainsString('Dialogvariante wechseln', $html);
        self::assertStringNotContainsString('Testansicht', $html);
        self::assertStringNotContainsString($this->mission->getTitle(), $html);
        $this->dispatch('scan', field: 23, factionId: $foreignFactionId);
        self::assertSame([], $progress->getScannedFields());
        $this->dispatch('page', 2, factionId: $foreignFactionId);
        self::assertSame(2, $progress->getLastPage());
    }

    /** @return list<array{AwayMissionResearchEnum}> */
    public static function researchSystems(): array
    {
        return array_map(static fn (AwayMissionResearchEnum $system): array => [$system], AwayMissionResearchEnum::cases());
    }

    #[DataProvider('researchSystems')]
    public function testRewardIncludesExactlyThePersistedResearchDataset(AwayMissionResearchEnum $system): void
    {
        $progress = $this->scanAllFields();
        $progress->setResearchSystem($system)->setLastPage(3);
        $this->em->flush();
        $before = $this->ship->getStorageSum();
        $this->dispatch('reward');
        self::assertSame($before + 6, $this->ship->getStorageSum());
        self::assertTrue($progress->isRewardReceived());
        foreach (AwayMissionResearchEnum::cases() as $candidate) {
            $storage = $this->ship->getStorage()->get($candidate->getCommodityId());
            self::assertSame($candidate === $system ? 1 : 0, $storage?->getAmount() ?? 0);
        }
        $this->dispatch('reward');
        self::assertSame($before + 6, $this->ship->getStorageSum());
        self::assertSame(1, $this->ship->getStorage()->get($system->getCommodityId())->getAmount());
    }

    public function testMissingDatasetCannotPayOnlyTheBaseReward(): void
    {
        $progress = $this->scanAllFields();
        $progress->setLastPage(3);
        $commodity = $this->em->find(Commodity::class, $progress->getResearchSystem()->getCommodityId());
        $this->em->remove($commodity);
        $this->em->flush();
        $before = $this->ship->getStorageSum();
        $information = $this->dispatch('reward');
        self::assertContains('Für diese Außenmission ist kein gültiger Forschungsdatensatz hinterlegt', $information->getInformations());
        self::assertFalse($progress->isRewardReceived());
        self::assertSame($before, $this->ship->getStorageSum());
    }

    public function testRewardFitsWhenExactlySixStorageUnitsAreFree(): void
    {
        $progress = $this->scanAllFields();
        $progress->setLastPage(3);
        $filler = $this->em->find(Commodity::class, 1);
        $amount = $this->ship->getMaxStorage() - $this->ship->getStorageSum() - 6;
        $this->getContainer()->get(StorageManagerInterface::class)->upperStorage($this->ship, $filler, $amount);
        $this->em->flush();
        self::assertSame(6, $this->ship->getMaxStorage() - $this->ship->getStorageSum());
        $this->dispatch('reward');
        self::assertTrue($progress->isRewardReceived());
        self::assertSame($this->ship->getMaxStorage(), $this->ship->getStorageSum());
        self::assertSame(1, $this->ship->getStorage()->get($progress->getResearchSystem()->getCommodityId())->getAmount());
    }

    public function testQuestCompletionDisablesMissionAndReleasesProtection(): void
    {
        $protection = new ColonyProtection()->setColony($this->mission->getColony())->setQuest($this->mission->getQuest());
        $this->em->persist($protection);
        $this->em->flush();
        $repository = $this->getContainer()->get(ColonyProtectionRepositoryInterface::class);
        self::assertTrue($repository->isProtected($this->mission->getColony()));
        $this->scanAllFields();
        $this->mission->getQuest()->setEnd(time());
        $this->em->flush();
        self::assertFalse($repository->isProtected($this->mission->getColony()));
        self::assertContains('An dieser Position ist keine Außenmission verfügbar', $this->dispatch('start')->getInformations());
    }

    public function testPermanentProtectionDoesNotDependOnQuestCompletion(): void
    {
        $protection = new ColonyProtection()->setColony($this->mission->getColony());
        $this->em->persist($protection);
        $this->mission->getQuest()->setEnd(time());
        $this->em->flush();
        self::assertTrue($this->getContainer()->get(ColonyProtectionRepositoryInterface::class)->isProtected($this->mission->getColony()));
    }

    public function testProtectedStarterIsExcludedUntilQuestEnds(): void
    {
        $colonies = $this->getContainer()->get(ColonyRepositoryInterface::class);
        $colony = $this->em->getRepository(Colony::class)->findOneBy(['user_id' => 1]);
        self::assertInstanceOf(Colony::class, $colony);
        $colony->getColonyClass()->setAllowStart(true);
        $map = $colony->getStarsystemMap()->getSystem()->getMap();
        self::assertNotNull($map);
        $map->setAdminRegionId(134);
        new ReflectionProperty($map->getLayer(), 'is_colonizable')->setValue($map->getLayer(), true);
        $this->em->persist(new MapRegionSettlement()->setRegionId(134)->setFactionId($this->user->getFactionId()));
        $this->em->flush();
        self::assertArrayHasKey($colony->getId(), $colonies->getStartingByFaction($this->user->getFactionId()));
        $this->em->persist(new ColonyProtection()->setColony($colony)->setQuest($this->mission->getQuest()));
        $this->em->flush();
        self::assertArrayNotHasKey($colony->getId(), $colonies->getStartingByFaction($this->user->getFactionId()));
        $this->mission->getQuest()->setEnd(time());
        $this->em->flush();
        self::assertArrayHasKey($colony->getId(), $colonies->getStartingByFaction($this->user->getFactionId()));
    }

    public function testMovingAwayPreventsFurtherScanning(): void
    {
        $progress = $this->initializeProgress();
        $this->ship->setLocation($this->em->getRepository(Map::class)->findOneBy([]));
        $this->em->flush();
        $this->dispatch('scan', field: 23);
        self::assertSame([], $progress->getScannedFields());
    }

    public function testBrokenMatrixScannerDoesNotPreventMissionScanning(): void
    {
        $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::MATRIX_SCANNER)->setStatus(0);
        $this->em->flush();
        $result = $this->openSurfaceScan();
        self::assertSame('html/ship/awayMission.twig', $result['macro']);
        $this->dispatch('page', 2);
        $this->dispatch('scan', field: 23);
        self::assertSame([23], $this->missions->getProgress($this->mission, $this->user)->getScannedFields());
    }

    public function testGetRequestCannotChangeProgress(): void
    {
        $progress = $this->initializeProgress();
        $_SERVER['REQUEST_METHOD'] = 'GET';
        request::setMockVars(['id' => 42, 'mission_action' => 'scan', 'field' => 23]);
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('setView')->with(ShowAwayMission::VIEW_IDENTIFIER)->once();
        $context->shouldNotReceive('getUser');
        $this->getContainer()->get(AwayMissionAction::class)->handle($context);
        self::assertSame([], $progress->getScannedFields());
    }

    public function testDeletionHandlerRemovesRelatedData(): void
    {
        $this->scanAllFields();
        $this->em->persist(new ColonyProtection()->setColony($this->mission->getColony())->setQuest($this->mission->getQuest()));
        $this->em->flush();
        $this->getContainer()->get(AwayMissionDeletionHandler::class)->deleteByQuest($this->mission->getQuest());
        foreach ([AwayMissionProgress::class, AwayMissionField::class, AwayMissionDialogue::class, AwayMission::class, ColonyProtection::class] as $class) {
            self::assertSame(0, $this->em->getRepository($class)->count([]));
        }
    }

    public function testPopupShowsErrorsAndEscapesDialogueWithoutAttackLinks(): void
    {
        $progress = $this->scanAllFields();
        $progress->setStarted(true)->setLastPage(1);
        $this->em->persist(new AwayMissionDialogue()->setMission($this->mission)->setPage(10)->setPosition(30)
            ->setSpeaker(CrewTypeEnum::SCIENCE)->setText('<script>alert(1)</script>'));
        $this->em->flush();
        $html = $this->renderPopupHtml($this->getPopupVariables($progress));
        self::assertStringContainsString('Zu wenig Lagerraum', $html);
        self::assertStringContainsString('&lt;script&gt;', $html);
        self::assertStringNotContainsString('<script>alert', $html);
        self::assertStringNotContainsString('B_ATTACK_BUILDING', $html);
        self::assertStringContainsString('away-mission-speech-right', $html);
    }

    public function testPopupRendersDatabaseAndNameBbcodeWithoutForcedItalics(): void
    {
        $progress = $this->scanAllFields();
        $progress->setStarted(true)->setLastPage(1);
        $this->ship->setName('[b]USS Test[/b]');
        $this->service->getCrew($this->ship)[1]->getCrew()->setName('[u]Aria[/u]');
        $entries = $this->missions->getDialogue($this->mission, $this->user->getFactionId());
        $entries[10][10]->setText('Erzähler: {ship.name} [u]Anlage[/u]');
        $entries[10][20]->setText('[i]Meldung[/i] von der {ship.name}');
        $html = $this->renderPopupHtml($this->getPopupVariables($progress));
        self::assertSame(2, substr_count($html, '<strong>USS Test</strong>'));
        self::assertStringContainsString('<u>Anlage</u>', $html);
        self::assertStringContainsString('<u>Aria</u>', $html);
        self::assertStringContainsString('<em>Meldung</em>', $html);
        self::assertStringNotContainsString('font-style: italic', $html);
        self::assertStringNotContainsString('[b]', $html);
        self::assertStringNotContainsString('Außenmission fortsetzen', $html);
    }

    public function testFinalPageOffersRewardOnlyUntilItHasBeenClaimed(): void
    {
        $progress = $this->scanAllFields();
        $progress->setStarted(true)->setLastPage(3);
        $html = $this->renderPopupHtml($this->getPopupVariables($progress, ['mission_page' => 3]));
        self::assertStringContainsString('Belohnung einsammeln', $html);
        self::assertStringNotContainsString('Außenteam zurück beamen', $html);
        self::assertStringNotContainsString('Belohnung:', $html);
        self::assertStringNotContainsString($this->mission->getRewardCommodity()->getName(), $html);
        $progress->setRewardReceived(true);
        $html = $this->renderPopupHtml($this->getPopupVariables($progress, ['mission_page' => 3]));
        self::assertStringNotContainsString('Belohnung einsammeln', $html);
    }

    public function testSurfaceUsesSegmentedProgressAndStandardButtons(): void
    {
        $progress = $this->scanAllFields();
        $html = $this->renderPopupHtml($this->getPopupVariables($progress));
        self::assertStringContainsString('away-mission-surface', $html);
        self::assertStringContainsString('role="progressbar"', $html);
        self::assertSame(2, substr_count($html, 'away-mission-scan-segment-complete'));
        self::assertStringContainsString('class="button"', $html);
        self::assertStringNotContainsString('<progress', $html);
        self::assertStringNotContainsString('width: 620px', $html);
    }

    /** @param array<string, int|string> $parameters
     * @return array<string, mixed>
     */
    private function getPopupVariables(AwayMissionProgress $progress, array $parameters = []): array
    {
        $variables = ['SESSIONSTRING' => 'token', 'INFORMATION' => ['Zu wenig Lagerraum']];
        $view = $this->mock(ViewControllerContext::class);
        $view->shouldReceive('setPageTitle');
        $view->shouldReceive('getInfo')->andReturn(new InformationWrapper());
        $view->shouldReceive('setMacroInAjaxWindow');
        $view->shouldReceive('setTemplateVar')->andReturnUsing(function (string $key, mixed $value) use (&$variables): void {
            $variables[$key] = $value;
        });
        request::setMockVars($parameters);
        $this->getContainer()->get(ShowAwayMission::class)->render($this->mission, $progress, $this->ship, $view);

        return $variables;
    }

    /** @param array<string, mixed> $variables */
    private function renderPopupHtml(array $variables): string
    {
        $twig = new Environment($this->getContainer()->get(Environment::class)->getLoader(), ['cache' => false]);
        $this->getContainer()->make(TwigHelper::class, ['environment' => $twig])->registerFiltersAndFunctions();

        return $twig->render('html/ship/awayMission.twig', $variables);
    }

    /** @return array{macro: ?string, variables: array<string, mixed>} */
    private function openSurfaceScan(): array
    {
        $macro = null;
        $variables = [];
        $view = $this->mock(ViewControllerContext::class);
        $view->shouldReceive('getUser')->andReturn($this->user);
        $view->shouldReceive('getInfo')->andReturn(new InformationWrapper());
        $view->shouldReceive('setPageTitle');
        $view->shouldReceive('setMacroInAjaxWindow')->andReturnUsing(function (string $value) use (&$macro): void {
            $macro = $value;
        });
        $view->shouldReceive('setTemplateVar')->andReturnUsing(function (string $key, mixed $value) use (&$variables): void {
            $variables[$key] = $value;
        });
        request::setMockVars(['id' => 42]);
        $this->em->wrapInTransaction(fn () => $this->getContainer()->get(ShowColonyScan::class)->handle($view));
        return ['macro' => $macro, 'variables' => $variables];
    }

    private function removeMatrixScanner(): void
    {
        $matrix = $this->ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::MATRIX_SCANNER);
        $this->ship->getSystems()->remove(SpacecraftSystemTypeEnum::MATRIX_SCANNER->value);
        $this->em->remove($matrix);
        $this->em->flush();
    }

    private function renderScanControls(): string
    {
        $twig = new Environment($this->getContainer()->get(Environment::class)->getLoader(), ['cache' => false]);
        $this->getContainer()->make(TwigHelper::class, ['environment' => $twig])->registerFiltersAndFunctions();
        $template = $twig->createTemplate("{% from 'html/spacecraft/control/control_mid.twig' import control_mid %}{{ control_mid(wrapper, null, null, false, null, colony, false, null, 0, null, false, activeMission) }}");
        return $template->render([
            'wrapper' => $this->getContainer()->get(SpacecraftWrapperFactoryInterface::class)->wrapShip($this->ship),
            'colony' => $this->mission->getColony(),
            'activeMission' => $this->getContainer()->get(AwayMissionAccess::class)->getAvailableMission($this->ship) !== null
        ]);
    }

    private function initializeProgress(): AwayMissionProgress
    {
        return $this->em->wrapInTransaction(function (): AwayMissionProgress {
            $progress = $this->missions->initializeProgress($this->mission, $this->user);
            $this->service->prepareProgress($progress, $this->ship);

            return $progress;
        });
    }

    private function scanAllFields(): AwayMissionProgress
    {
        $progress = $this->initializeProgress();
        $this->dispatch('page', 2);
        $this->dispatch('scan', field: 23);
        $this->dispatch('scan', field: 24);

        return $progress;
    }

    private function dispatch(string $action, int $page = 0, int $field = 0, ?int $factionId = null): InformationWrapper
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        request::setMockVars(['id' => 42, 'mission_action' => $action, 'mission_page' => $page, 'field' => $field, 'mission_faction' => $factionId ?? $this->user->getFactionId()]);
        $information = new InformationWrapper();
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('getUser')->andReturn($this->user);
        $context->shouldReceive('getInfo')->andReturn($information);
        $this->views = [];
        $context->shouldReceive('setView')->andReturnUsing(function (string $view): void {
            $this->views[] = $view;
        });
        $this->em->wrapInTransaction(fn () => $this->getContainer()->get(AwayMissionAction::class)->handle($context));

        return $information;
    }
}
