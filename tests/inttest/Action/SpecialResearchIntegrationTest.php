<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use request;
use Stu\ActionTestCase;
use Stu\Component\Building\BuildingActivationHandler;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Building\BuildingRemovalHandler;
use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;
use Stu\Component\Research\SpecialResearchCatalog;
use Stu\Component\Research\SpecialResearchCompletion;
use Stu\Component\Research\SpecialResearchDefinitionDeletionHandler;
use Stu\Component\Research\SpecialResearchDeletionHandler;
use Stu\Component\Research\SpecialResearchFacility;
use Stu\Component\Research\SpecialResearchFailure;
use Stu\Component\Research\SpecialResearchService;
use Stu\Component\Research\SpecialResearchStatusEnum;
use Stu\Exception\AccessViolationException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Action\StartSpecialResearch\StartSpecialResearch;
use Stu\Module\Colony\Lib\Gui\Component\SpecialResearchProvider;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Template\TemplateInterface;
use Stu\Module\Tick\Process\FinishSpecialResearchJobs;
use Stu\Module\Twig\TwigHelper;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\BuildingFunction;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\SpecialResearch;
use Stu\Orm\Entity\SpecialResearchCost;
use Stu\Orm\Entity\SpecialResearchDefinition;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\SpecialResearchRepositoryInterface;
use Twig\Environment;

final class SpecialResearchIntegrationTest extends ActionTestCase
{
    private EntityManagerInterface $em;
    private Colony $colony;
    private PlanetField $field;
    private SpecialResearchService $service;
    private SpecialResearchRepositoryInterface $repository;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $container = $this->getContainer();
        $this->em = $container->get(EntityManagerInterface::class);
        $this->colony = $this->em->find(Colony::class, 42);
        $this->field = array_find($this->colony->getPlanetFields()->toArray(), static fn (PlanetField $field): bool => $field->getBuilding() !== null);
        $this->field->setActive(1);
        $building = $this->field->getBuilding();
        $function = new BuildingFunction()->setBuildingId($building->getId())->setFunction(BuildingFunctionEnum::SPECIAL_RESEARCH);
        $this->em->getClassMetadata(BuildingFunction::class)->setFieldValue($function, 'building', $building);
        $this->em->persist($function);
        $this->service = $container->get(SpecialResearchService::class);
        $this->repository = $container->get(SpecialResearchRepositoryInterface::class);
        $this->createRecipes();
        $this->em->flush();
    }

    private function createRecipes(): void
    {
        $recipes = [
            [1, AwayMissionResearchEnum::ENERGY_WEAPONS, 1, 77, [10701 => 10, 11701 => 10, 10702 => 5, 11702 => 5]],
            [2, AwayMissionResearchEnum::ENERGY_WEAPONS, 2, 77, [10721 => 10, 11721 => 10, 10722 => 5, 11722 => 5]],
            [3, AwayMissionResearchEnum::ENERGY_WEAPONS, 3, 77, [10731 => 10, 11731 => 10, 10732 => 5, 11732 => 5]],
            [4, AwayMissionResearchEnum::ENERGY_WEAPONS, 4, 77, [10741 => 10, 11741 => 10, 10742 => 5, 11742 => 5]],
            [5, AwayMissionResearchEnum::ENERGY_WEAPONS, 5, 77, [10751 => 10, 11751 => 10, 10752 => 5, 11752 => 5]],
            [6, AwayMissionResearchEnum::SHIELDS, null, 78, [10201 => 20, 10202 => 10]],
            [7, AwayMissionResearchEnum::EPS, null, 79, [10301 => 20, 10302 => 10]],
            [8, AwayMissionResearchEnum::REACTOR, null, 80, [10501 => 20, 10502 => 10]],
            [9, AwayMissionResearchEnum::IMPULSE_DRIVE, null, 89, [10401 => 20, 10402 => 10]],
            [10, AwayMissionResearchEnum::WARP_DRIVE, null, 90, [10901 => 20, 10902 => 10]],
            [11, AwayMissionResearchEnum::SENSORS, null, 91, [10601 => 20, 10602 => 10]],
            [12, AwayMissionResearchEnum::COMPUTER, null, 92, [11901 => 20, 11902 => 10]]
        ];
        $metadata = $this->em->getClassMetadata(Commodity::class);
        $generatorType = $metadata->generatorType;
        $generator = $metadata->idGenerator;
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());
        try {
            foreach ($recipes as [$id, $type, $faction, $chip, $modules]) {
                $definition = new SpecialResearchDefinition()->setId($id)->setResearchType($type)
                    ->setFaction($faction === null ? null : $this->em->find(Faction::class, $faction))
                    ->setDataCommodity($this->commodity($chip, 'Datensatz ' . $type->getDescription()))
                    ->setDataAmount(1)->setDuration(345600)->setFailureChance(10);
                $this->em->persist($definition);
                foreach ([76 => 5] + $modules as $commodityId => $amount) {
                    $cost = new SpecialResearchCost()->setDefinition($definition)
                        ->setCommodity($this->commodity($commodityId, 'Material ' . $commodityId))->setAmount($amount);
                    $definition->getCosts()->add($cost);
                    $this->em->persist($cost);
                }
            }
            $this->em->flush();
        } finally {
            $metadata->setIdGeneratorType($generatorType);
            $metadata->setIdGenerator($generator);
        }
    }

    private function commodity(int $id, string $name): Commodity
    {
        $commodity = $this->em->find(Commodity::class, $id);
        if ($commodity === null) {
            $commodity = new Commodity()->setName($name);
            $this->em->getClassMetadata(Commodity::class)->setFieldValue($commodity, 'id', $id);
            $this->em->persist($commodity);
            $this->em->flush();
        }
        return $commodity;
    }

    private function definition(AwayMissionResearchEnum $type, int $factionId = 1): SpecialResearchDefinition
    {
        return $this->getContainer()->get(SpecialResearchCatalog::class)->getDefinition($type, $factionId)
            ?? throw new \LogicException('Test research definition missing');
    }

    /** @return iterable<string, array{AwayMissionResearchEnum}> */
    public static function researchTypes(): iterable
    {
        foreach (AwayMissionResearchEnum::cases() as $type) {
            yield $type->value => [$type];
        }
    }

    #[DataProvider('researchTypes')]
    public function testEachResearchTypeCanStartWithItsMaterials(AwayMissionResearchEnum $type): void
    {
        $this->supply($type);
        $before = $this->colony->getStorageSum();
        self::assertTrue($this->start($type));
        self::assertSame($before - 36, $this->colony->getStorageSum());
        $research = $this->repository->getByUser($this->colony->getUser())[0];
        self::assertSame($type, $research->getResearchType());
        self::assertSame(SpecialResearchStatusEnum::RUNNING, $research->getStatus());
    }

    public function testControllerRejectsColonyOwnedByAnotherPlayer(): void
    {
        $previousMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        request::setMockVars(['id' => 42, 'research_type' => 'sensors']);
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('getUser')->andReturn($this->em->find(User::class, 102));
        $context->shouldReceive('setView');
        $context->shouldReceive('setViewContext');
        try {
            $this->expectException(AccessViolationException::class);
            $this->getContainer()->get(StartSpecialResearch::class)->handle($context);
        } finally {
            request::setMockVars(null);
            if ($previousMethod === null) {
                unset($_SERVER['REQUEST_METHOD']);
            } else {
                $_SERVER['REQUEST_METHOD'] = $previousMethod;
            }
        }
    }

    public function testStartConsumesExactlyOneRecipeAndPreventsAnotherResearch(): void
    {
        $this->supply(AwayMissionResearchEnum::SENSORS, 2);
        $this->supply(AwayMissionResearchEnum::EPS);
        $before = $this->colony->getStorageSum();
        self::assertTrue($this->start(AwayMissionResearchEnum::SENSORS));
        $research = $this->repository->getByUser($this->colony->getUser())[0];
        self::assertSame(345600, $research->getFinishTime() - $research->getStartTime());
        self::assertSame($this->colony, $research->getColony());
        self::assertSame(SpecialResearchStatusEnum::RUNNING, $research->getStatus());
        self::assertSame($before - 36, $this->colony->getStorageSum());
        self::assertSame(20, $this->colony->getStorage()->get(10601)->getAmount());
        self::assertSame(10, $this->colony->getStorage()->get(10602)->getAmount());
        self::assertFalse($this->start(AwayMissionResearchEnum::EPS));
        self::assertFalse($this->start(AwayMissionResearchEnum::SENSORS));
        self::assertSame($before - 36, $this->colony->getStorageSum());
        self::assertCount(1, $this->repository->getByUser($this->colony->getUser()));
    }

    public function testRunningResearchAlsoBlocksStartOnAnotherColony(): void
    {
        $this->supply(AwayMissionResearchEnum::SENSORS);
        $research = $this->newResearch(AwayMissionResearchEnum::EPS, SpecialResearchStatusEnum::RUNNING);
        $research->setColony(null);
        $this->em->persist($research);
        $this->em->flush();
        $before = $this->colony->getStorageSum();
        self::assertFalse($this->start(AwayMissionResearchEnum::SENSORS));
        self::assertSame($before, $this->colony->getStorageSum());
    }

    /** @return iterable<string, array{int}> */
    public static function missingMaterials(): iterable
    {
        foreach ([76, 91, 10601, 10602] as $id) {
            yield (string) $id => [$id];
        }
    }

    #[DataProvider('missingMaterials')]
    public function testMissingMaterialDoesNotConsumeOtherResources(int $missingId): void
    {
        $this->supply(AwayMissionResearchEnum::SENSORS);
        $storage = $this->colony->getStorage()->get($missingId);
        $this->getContainer()->get(StorageManagerInterface::class)->lowerStorage($this->colony, $storage->getCommodity(), $storage->getAmount());
        $this->em->flush();
        $before = $this->colony->getStorageSum();
        self::assertFalse($this->start(AwayMissionResearchEnum::SENSORS));
        self::assertSame($before, $this->colony->getStorageSum());
        self::assertSame([], $this->repository->getByUser($this->colony->getUser()));
    }

    public function testInactiveResearchBuildingPreventsStart(): void
    {
        $this->supply(AwayMissionResearchEnum::SENSORS);
        $this->field->setActive(0);
        $this->em->flush();
        $before = $this->colony->getStorageSum();
        self::assertFalse($this->start(AwayMissionResearchEnum::SENSORS));
        self::assertSame($before, $this->colony->getStorageSum());
    }

    /** @return iterable<array{int}> */
    public static function playableFactions(): iterable
    {
        foreach (range(1, 5) as $id) {
            yield [$id];
        }
    }

    #[DataProvider('playableFactions')]
    public function testEachFactionUsesItsOwnWeaponRecipe(int $factionId): void
    {
        $this->colony->getUser()->setFaction($this->em->find(Faction::class, $factionId));
        $this->em->flush();
        $this->supply(AwayMissionResearchEnum::ENERGY_WEAPONS, factionId: $factionId);
        $before = $this->colony->getStorageSum();
        self::assertTrue($this->start(AwayMissionResearchEnum::ENERGY_WEAPONS));
        self::assertSame($before - 36, $this->colony->getStorageSum());
    }

    public function testForeignWeaponModulesCannotPayForOwnFactionResearch(): void
    {
        $this->supply(AwayMissionResearchEnum::ENERGY_WEAPONS);
        $this->colony->getUser()->setFaction($this->em->find(Faction::class, 3));
        $this->em->flush();
        $before = $this->colony->getStorageSum();
        self::assertFalse($this->start(AwayMissionResearchEnum::ENERGY_WEAPONS));
        self::assertSame($before, $this->colony->getStorageSum());
    }

    public function testCompletedFundamentalsCannotBeResearchedAgain(): void
    {
        $this->supply(AwayMissionResearchEnum::SENSORS);
        $this->em->persist($this->newResearch(AwayMissionResearchEnum::SENSORS, SpecialResearchStatusEnum::COMPLETED));
        $this->em->flush();
        $before = $this->colony->getStorageSum();
        self::assertFalse($this->start(AwayMissionResearchEnum::SENSORS));
        self::assertSame($before, $this->colony->getStorageSum());
    }

    public function testFailedResearchCanBeRetriedWithNewMaterials(): void
    {
        $this->supply(AwayMissionResearchEnum::SENSORS);
        $research = $this->newResearch(AwayMissionResearchEnum::SENSORS, SpecialResearchStatusEnum::FAILED);
        $this->em->persist($research);
        $this->em->flush();
        $id = $research->getId();
        self::assertTrue($this->start(AwayMissionResearchEnum::SENSORS));
        self::assertSame($id, $research->getId());
        self::assertSame(SpecialResearchStatusEnum::RUNNING, $research->getStatus());
        self::assertSame(345600, $research->getFinishTime() - $research->getStartTime());
    }

    /** @return iterable<string, array{int, SpecialResearchStatusEnum}> */
    public static function outcomes(): iterable
    {
        yield 'first failure' => [1, SpecialResearchStatusEnum::FAILED];
        yield 'last failure' => [10, SpecialResearchStatusEnum::FAILED];
        yield 'first success' => [11, SpecialResearchStatusEnum::COMPLETED];
        yield 'last success' => [100, SpecialResearchStatusEnum::COMPLETED];
    }

    #[DataProvider('outcomes')]
    public function testProcessTickFinishesOnlyDueResearchOnce(int $roll, SpecialResearchStatusEnum $expected): void
    {
        $research = $this->newResearch(AwayMissionResearchEnum::SENSORS, SpecialResearchStatusEnum::RUNNING)->setFinishTime(900);
        $future = $this->newResearch(AwayMissionResearchEnum::EPS, SpecialResearchStatusEnum::RUNNING)->setFinishTime(1001);
        $this->em->persist($research);
        $this->em->persist($future);
        $this->em->flush();
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(1, 100)->andReturn($roll);
        $time = $this->mock(StuTime::class);
        $time->shouldReceive('time')->times(3)->andReturn(1000);
        $messages = $this->mock(PrivateMessageSenderInterface::class);
        $messages->shouldReceive('send')->once()->with(1, 101, Mockery::type('string'), PrivateMessageFolderTypeEnum::SPECIAL_COLONY);
        $process = new FinishSpecialResearchJobs(
            $this->repository,
            $random,
            $time,
            new SpecialResearchCompletion($this->repository, $messages, $time),
            $this->getContainer()->get(SpecialResearchFacility::class),
            $this->getContainer()->get(SpecialResearchFailure::class)
        );
        $this->em->wrapInTransaction(fn () => $process->work());
        $this->em->wrapInTransaction(fn () => $process->work());
        self::assertSame($expected, $research->getStatus());
        self::assertSame(SpecialResearchStatusEnum::RUNNING, $future->getStatus());
        self::assertSame(1000, $research->getFinishTime());
        self::assertSame(1001, $future->getFinishTime());
    }

    public function testDeletingColonyPreservesUserProgressAndDeletingPlayerRemovesIt(): void
    {
        $research = $this->newResearch(AwayMissionResearchEnum::SENSORS, SpecialResearchStatusEnum::COMPLETED);
        $this->em->persist($research);
        $this->em->flush();
        $handler = $this->getContainer()->get(SpecialResearchDeletionHandler::class);
        $handler->detachColony($this->colony);
        $this->em->flush();
        $this->em->refresh($research);
        self::assertNull($research->getColony());
        self::assertSame(SpecialResearchStatusEnum::COMPLETED, $research->getStatus());
        $handler->delete($this->colony->getUser());
        self::assertSame([], $this->repository->getByUser($this->colony->getUser()));
    }

    public function testRecipeChangesInDatabaseControlMaterialsDurationAndRisk(): void
    {
        $definition = $this->definition(AwayMissionResearchEnum::SENSORS);
        $definition->setDataCommodity($this->em->find(Commodity::class, 2))->setDataAmount(2)
            ->setDuration(172800)->setFailureChance(37);
        foreach ($definition->getCosts() as $cost) {
            $cost->setAmount(match ($cost->getCommodity()->getId()) {
                76 => 7,
                10601 => 3,
                default => 2
            });
        }
        $this->em->flush();
        $this->supply(AwayMissionResearchEnum::SENSORS);
        $html = $this->renderPanel();
        self::assertStringContainsString('2 Tage', $html);
        self::assertStringContainsString('37%', $html);
        $before = $this->colony->getStorageSum();
        self::assertTrue($this->start(AwayMissionResearchEnum::SENSORS));
        self::assertSame($before - 14, $this->colony->getStorageSum());
        $research = $this->repository->getByUser($this->colony->getUser())[0];
        self::assertSame(172800, $research->getFinishTime() - $research->getStartTime());
        self::assertSame(37, $research->getFailureChance());
        $definition->setDuration(1)->setFailureChance(100);
        $this->em->flush();
        self::assertSame(37, $research->getFailureChance());
        self::assertSame(172800, $research->getFinishTime() - $research->getStartTime());
    }

    public function testMissingRecipePreventsStartWithoutConsumingMaterials(): void
    {
        $this->supply(AwayMissionResearchEnum::SENSORS);
        $this->getContainer()->get(SpecialResearchDefinitionDeletionHandler::class)
            ->delete($this->definition(AwayMissionResearchEnum::SENSORS));
        $this->em->flush();
        $before = $this->colony->getStorageSum();
        self::assertFalse($this->start(AwayMissionResearchEnum::SENSORS));
        self::assertSame($before, $this->colony->getStorageSum());
        self::assertSame([], $this->repository->getByUser($this->colony->getUser()));
    }

    public function testInvalidRecipePreventsStartWithoutConsumingMaterials(): void
    {
        $this->supply(AwayMissionResearchEnum::SENSORS);
        $cost = $this->definition(AwayMissionResearchEnum::SENSORS)->getCosts()->first();
        self::assertInstanceOf(SpecialResearchCost::class, $cost);
        $cost->setAmount(-1);
        $this->em->flush();
        $before = $this->colony->getStorageSum();
        self::assertFalse($this->start(AwayMissionResearchEnum::SENSORS));
        self::assertSame($before, $this->colony->getStorageSum());
    }

    public function testProcessUsesRiskStoredWhenResearchStarted(): void
    {
        $research = $this->newResearch(AwayMissionResearchEnum::SENSORS, SpecialResearchStatusEnum::RUNNING)
            ->setFailureChance(37)->setFinishTime(1000);
        $this->em->persist($research);
        $this->definition(AwayMissionResearchEnum::SENSORS)->setFailureChance(0);
        $this->em->flush();
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(1, 100)->andReturn(37);
        $time = $this->mock(StuTime::class);
        $time->shouldReceive('time')->twice()->andReturn(1000);
        $messages = $this->mock(PrivateMessageSenderInterface::class);
        $messages->shouldReceive('send')->once()->with(1, 101, Mockery::type('string'), PrivateMessageFolderTypeEnum::SPECIAL_COLONY);
        $process = new FinishSpecialResearchJobs(
            $this->repository,
            $random,
            $time,
            new SpecialResearchCompletion($this->repository, $messages, $time),
            $this->getContainer()->get(SpecialResearchFacility::class),
            $this->getContainer()->get(SpecialResearchFailure::class)
        );
        $this->em->wrapInTransaction(fn () => $process->work());
        self::assertSame(SpecialResearchStatusEnum::FAILED, $research->getStatus());
    }

    public function testPanelShowsRecipeAndHidesStartAfterCompletion(): void
    {
        $this->supply(AwayMissionResearchEnum::SENSORS);
        $html = $this->renderPanel();
        self::assertStringContainsString('Datensatz Sensoren', $html);
        self::assertStringContainsString('B_START_SPECIAL_RESEARCH', $html);
        self::assertStringNotContainsString('Datensatz EPS-Gitter', $html);
        $this->em->persist($this->newResearch(AwayMissionResearchEnum::SENSORS, SpecialResearchStatusEnum::COMPLETED));
        $this->em->flush();
        $html = $this->renderPanel();
        self::assertStringContainsString('Abgeschlossene Forschungen (1)', $html);
        self::assertStringContainsString('<td>Grundlagenforschung</td>', $html);
        self::assertStringContainsString('<td>Sensoren</td>', $html);
        self::assertStringNotContainsString('Erfolgreich abgeschlossen', $html);
        self::assertStringNotContainsString('<div class="divhead">Grundlagenforschung: Sensoren</div>', $html);
        self::assertStringNotContainsString('B_START_SPECIAL_RESEARCH', $html);
    }

    public function testNewResearchStoresTheExactFieldBuildingAndFailureSettings(): void
    {
        $this->supply(AwayMissionResearchEnum::SENSORS);
        self::assertTrue($this->start(AwayMissionResearchEnum::SENSORS));
        $research = $this->repository->getByUser($this->colony->getUser())[0];
        self::assertSame($this->field->getId(), $research->getFieldId());
        self::assertSame($this->field->getBuildingId(), $research->getBuildingId());
        self::assertSame($this->definition(AwayMissionResearchEnum::SENSORS)->getFailureSettings(), $research->getFailureSettings());
    }

    public function testAccidentDeactivatesAndDamagesTheLaboratoryBeforeRemovingWorkers(): void
    {
        $this->prepareAccidentSite(20);
        $research = $this->boundResearch();
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(5, 15)->andReturn(7);
        $message = $this->accident($random)->apply($research);
        $this->em->flush();
        self::assertFalse($this->field->isActive());
        self::assertSame(850, $this->field->getIntegrity());
        self::assertSame(0, $this->colony->getWorkers());
        self::assertSame(13, $this->colony->getChangeable()->getWorkless());
        self::assertStringContainsString('7 Arbeiter', $message);
        self::assertStringContainsString('Sicherheitskräften getötet', $message);
    }

    public function testWorkerlessBuildingUsesFreePopulationAndCapsDeaths(): void
    {
        $this->prepareAccidentSite(0, free: 2);
        $research = $this->boundResearch();
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(3, 12)->andReturn(12);
        $message = $this->accident($random)->apply($research);
        self::assertSame(0, $this->colony->getPopulation());
        self::assertFalse($this->field->isActive());
        self::assertSame(850, $this->field->getIntegrity());
        self::assertStringContainsString('2 Personen der freien Bevölkerung', $message);
    }

    /** @return iterable<array{int}> */
    public static function cardinalNeighbors(): iterable
    {
        foreach ([5, 25, 14, 16] as $index) {
            yield [$index];
        }
    }

    #[DataProvider('cardinalNeighbors')]
    public function testWorkerlessEmptyColonyUsesExactlyOneCardinalNeighbor(int $index): void
    {
        $this->prepareAccidentSite(0);
        $neighbor = $this->addAccidentBuilding($index, 20);
        $other = $this->addAccidentBuilding(26, 20);
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('array_rand')->once()->andReturn($index);
        $random->shouldReceive('rand')->once()->with(5, 15)->andReturn(8);
        $message = $this->accident($random)->apply($this->boundResearch());
        self::assertFalse($neighbor->isActive());
        self::assertTrue($other->isActive());
        self::assertSame(1000, $neighbor->getIntegrity());
        self::assertSame(20, $this->colony->getWorkers());
        self::assertSame(12, $this->colony->getChangeable()->getWorkless());
        self::assertStringContainsString('benachbarte Gebäude', $message);
        self::assertStringContainsString('8 Arbeiter', $message);
    }

    /** @return iterable<string, array{int, int}> */
    public static function nonNeighbors(): iterable
    {
        yield 'diagonal' => [15, 26];
        yield 'right edge' => [10, 11];
        yield 'left edge' => [11, 10];
    }

    #[DataProvider('nonNeighbors')]
    public function testAccidentDoesNotCrossDiagonalsOrGridEdges(int $source, int $other): void
    {
        $this->prepareAccidentSite(0, $source);
        $neighbor = $this->addAccidentBuilding($other, 20);
        $random = $this->mock(StuRandom::class);
        $random->shouldNotReceive('rand', 'array_rand');
        $message = $this->accident($random)->apply($this->boundResearch());
        self::assertTrue($neighbor->isActive());
        self::assertSame(20, $this->colony->getPopulation());
        self::assertStringContainsString('Es gab keine Opfer', $message);
    }

    public function testLegacyFailureChoosesAndRecordsOnlyOneResearchBuilding(): void
    {
        $this->prepareAccidentSite(20);
        $other = $this->addAccidentBuilding(16, 20, true);
        $research = $this->newResearch(AwayMissionResearchEnum::SENSORS, SpecialResearchStatusEnum::RUNNING);
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(5, 15)->andReturn(5);
        $this->accident($random)->apply($research);
        self::assertSame($this->field->getId(), $research->getFieldId());
        self::assertSame(850, $this->field->getIntegrity());
        self::assertSame(1000, $other->getIntegrity());
        self::assertTrue($other->isActive());
    }

    public function testAccidentUsesSettingsStoredAtResearchStart(): void
    {
        $this->prepareAccidentSite(20);
        $definition = $this->definition(AwayMissionResearchEnum::SENSORS);
        $definition->setFailureDamagePercent(30)->setFailureWorkerMinPercent(50)->setFailureWorkerMaxPercent(50);
        $research = $this->boundResearch();
        $definition->setFailureDamagePercent(1)->setFailureWorkerMinPercent(0)->setFailureWorkerMaxPercent(0);
        $random = $this->mock(StuRandom::class);
        $random->shouldReceive('rand')->once()->with(10, 10)->andReturn(10);
        $this->accident($random)->apply($research);
        self::assertSame(700, $this->field->getIntegrity());
        self::assertSame(10, $this->colony->getPopulation());
    }

    /** @return iterable<array{bool}> */
    public static function removalModes(): iterable
    {
        yield [false];
        yield [true];
    }

    #[DataProvider('removalModes')]
    public function testDemolitionAndDestructionFailOnlyResearchAtThatField(bool $destroyed): void
    {
        $this->prepareAccidentSite(20);
        $research = $this->boundResearch()->setFinishTime(2000000000);
        $otherField = $this->addAccidentBuilding(16, 0, true);
        $other = $this->newResearch(AwayMissionResearchEnum::EPS, SpecialResearchStatusEnum::RUNNING)->setField($otherField);
        $this->em->persist($research);
        $this->em->persist($other);
        $this->em->flush();
        $messages = $this->mock(PrivateMessageSenderInterface::class);
        $messages->shouldReceive('send')->once()->with(1, 101, Mockery::on(static fn (string $text): bool => str_contains($text, 'entfernt, zerstört') && !str_contains($text, 'assimiliert')), PrivateMessageFolderTypeEnum::SPECIAL_COLONY);
        $completion = new SpecialResearchCompletion($this->repository, $messages, $this->getContainer()->get(StuTime::class));
        $removal = $this->getContainer()->make(BuildingRemovalHandler::class, ['researchCompletion' => $completion]);
        if ($destroyed) {
            $this->field->setIntegrity(0);
        }
        $removal->remove($this->field);
        $this->em->flush();
        self::assertSame(SpecialResearchStatusEnum::FAILED, $research->getStatus());
        self::assertSame(SpecialResearchStatusEnum::RUNNING, $other->getStatus());
        self::assertNull($this->field->getBuilding());
        self::assertSame(20, $this->colony->getPopulation());
    }

    public function testTickFailsMissingFieldBeforeDeadlineWithoutDamagingReplacement(): void
    {
        $this->prepareAccidentSite(20);
        $research = $this->boundResearch()->setFinishTime(2000);
        $this->em->persist($research);
        $this->em->flush();
        $replacement = $this->addAccidentBuilding(15, 0, true);
        $random = $this->mock(StuRandom::class);
        $random->shouldNotReceive('rand');
        $time = $this->mock(StuTime::class);
        $time->shouldReceive('time')->once()->andReturn(1000);
        $messages = $this->mock(PrivateMessageSenderInterface::class);
        $messages->shouldReceive('send')->once()->with(1, 101, Mockery::type('string'), PrivateMessageFolderTypeEnum::SPECIAL_COLONY);
        $process = new FinishSpecialResearchJobs(
            $this->repository,
            $random,
            $time,
            new SpecialResearchCompletion($this->repository, $messages, $time),
            $this->getContainer()->get(SpecialResearchFacility::class),
            $this->accident($random)
        );
        $this->em->wrapInTransaction(fn () => $process->work());
        self::assertSame(SpecialResearchStatusEnum::FAILED, $research->getStatus());
        self::assertSame(1000, $research->getFinishTime());
        self::assertSame(1000, $replacement->getIntegrity());
        self::assertTrue($replacement->isActive());
    }

    private function prepareAccidentSite(int $workers, int $index = 15, int $free = 0): void
    {
        foreach ($this->colony->getPlanetFields() as $field) {
            $field->setBuilding(null)->setActive(0);
        }
        $this->colony->setSurfaceWidth(10);
        $this->colony->getChangeable()->setWorkers(0)->setWorkless($free);
        $this->field = $this->addAccidentBuilding($index, $workers, true);
    }

    private function addAccidentBuilding(int $index, int $workers, bool $research = false): PlanetField
    {
        $building = new Building()->setName('Testgebäude ' . $index)->setWorkers($workers)->setIntegrity(1000)->setIsActivateable(true);
        $this->em->persist($building);
        $this->em->flush();
        if ($research) {
            $function = new BuildingFunction()->setBuildingId($building->getId())->setFunction(BuildingFunctionEnum::SPECIAL_RESEARCH);
            $this->em->getClassMetadata(BuildingFunction::class)->setFieldValue($function, 'building', $building);
            $this->em->persist($function);
            $building->getFunctions()->set(BuildingFunctionEnum::SPECIAL_RESEARCH->value, $function);
        }
        $field = $this->colony->getPlanetFields()->get($index);
        if ($field === null) {
            $field = new PlanetField()->setColony($this->colony)->setFieldId($index);
            $this->colony->getPlanetFields()->set($index, $field);
            $this->em->persist($field);
        }
        $field->setBuilding($building)->setActive(1)->setIntegrity(1000);
        $this->colony->getChangeable()->setWorkers($this->colony->getWorkers() + $workers);
        $this->em->flush();
        return $field;
    }

    private function boundResearch(): SpecialResearch
    {
        return $this->newResearch(AwayMissionResearchEnum::SENSORS, SpecialResearchStatusEnum::RUNNING)
            ->setField($this->field)->setFailureSettings($this->definition(AwayMissionResearchEnum::SENSORS)->getFailureSettings());
    }

    private function accident(StuRandom $random): SpecialResearchFailure
    {
        return new SpecialResearchFailure(
            $this->getContainer()->get(SpecialResearchFacility::class),
            $this->getContainer()->get(SpecialResearchCatalog::class),
            $this->getContainer()->get(BuildingActivationHandler::class),
            $this->getContainer()->get(PlanetFieldRepositoryInterface::class),
            $random
        );
    }

    private function renderPanel(): string
    {
        $variables = ['HOST' => $this->colony, 'SESSIONSTRING' => 'test'];
        $template = $this->mock(TemplateInterface::class);
        $template->shouldReceive('setTemplateVar')->andReturnUsing(function (string $key, mixed $value) use (&$variables): void {
            $variables[$key] = $value;
        });
        $this->getContainer()->get(SpecialResearchProvider::class)->setTemplateVariables($this->colony, $template, $this->colony->getUser());
        $twig = new Environment($this->getContainer()->get(Environment::class)->getLoader(), ['cache' => false]);
        $this->getContainer()->make(TwigHelper::class, ['environment' => $twig])->registerFiltersAndFunctions();
        return $twig->render('html/colony/menu/specialResearch.twig', $variables);
    }

    private function newResearch(AwayMissionResearchEnum $type, SpecialResearchStatusEnum $status): SpecialResearch
    {
        return new SpecialResearch()->setUser($this->colony->getUser())->setColony($this->colony)
            ->setResearchType($type)->setStatus($status)->setStartTime(1)->setFinishTime(2)->setFailureChance(10);
    }

    private function supply(AwayMissionResearchEnum $type, int $attempts = 1, int $factionId = 1): void
    {
        foreach ($this->getContainer()->get(SpecialResearchCatalog::class)->getCosts($this->definition($type, $factionId)) as $id => $amount) {
            $this->getContainer()->get(StorageManagerInterface::class)->upperStorage($this->colony, $this->em->find(Commodity::class, $id), $amount * $attempts);
        }
        $this->em->flush();
    }

    private function start(AwayMissionResearchEnum $type): bool
    {
        return $this->em->wrapInTransaction(fn (): bool => $this->service->start($this->colony, $this->field, $type, new InformationWrapper()));
    }
}
