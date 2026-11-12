<?php

declare(strict_types=1);

namespace Stu\Action;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Mapping\ClassMetadata;
use Override;
use request;
use Stu\ActionTestCase;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;
use Stu\Component\Research\ModuleOptimizationProgress;
use Stu\Component\Research\SpecialResearchCompletion;
use Stu\Component\Research\SpecialResearchStatusEnum;
use Stu\Component\Research\TechnologyJobFactory;
use Stu\Component\Research\TechnologyRecipeKindEnum;
use Stu\Component\Research\TechnologyService;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Colony\Action\CancelModuleCreation\CancelModuleCreation;
use Stu\Module\Colony\Action\CreateModules\CreateModules;
use Stu\Module\Colony\Lib\Gui\Component\SpecialResearchProvider;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Colony\View\ShowModuleFab\ShowModuleFab;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\StuTime;
use Stu\Module\Template\TemplateInterface;
use Stu\Module\Tick\Colony\ColonyTickInterface;
use Stu\Module\Tick\Process\FinishTechnologyJobs;
use Stu\Module\Tick\Spacecraft\SpacecraftTickManagerInterface;
use Stu\Module\Twig\TwigHelper;
use Stu\Orm\Entity\BuildingFunction;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ModuleBuildingFunction;
use Stu\Orm\Entity\ModuleCost;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\Research;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\SpecialResearch;
use Stu\Orm\Entity\SpecialResearchCost;
use Stu\Orm\Entity\SpecialResearchDefinition;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Entity\TechnologyRecipeCost;
use Stu\Orm\Entity\TechnologyResearch;
use Stu\Orm\Entity\UserModuleOptimization;
use Stu\Orm\Repository\ModuleQueueRepositoryInterface;
use Stu\Orm\Repository\TechnologyRepositoryInterface;
use Twig\Environment;

final class TechnologyResearchIntegrationTest extends ActionTestCase
{
    private EntityManagerInterface $em;
    private Colony $colony;
    private PlanetField $field;
    private TechnologyRepositoryInterface $repository;
    private TechnologyService $service;
    private Module $naniteModule;

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
        foreach ([BuildingFunctionEnum::SPECIAL_RESEARCH, BuildingFunctionEnum::TECH_CENTER] as $type) {
            if (!$building->getFunctions()->containsKey($type->value)) {
                $function = new BuildingFunction()->setBuildingId($building->getId())->setFunction($type);
                $this->em->getClassMetadata(BuildingFunction::class)->setFieldValue($function, 'building', $building);
                $building->getFunctions()->set($type->value, $function);
                $this->em->persist($function);
            }
        }
        $this->colony->getChangeable()->setMaxStorage(10000)->setEps(1000);
        $this->seed();
        $this->repository = $container->get(TechnologyRepositoryInterface::class);
        $this->service = $container->get(TechnologyService::class);
        $this->em->flush();
        $this->naniteModule = $this->seedNaniteModule();
    }

    private function seed(): void
    {
        $chip = $this->commodity(91);
        $nanites = $this->commodity(76);
        $foundation = new SpecialResearchDefinition()->setId(1)->setResearchType(AwayMissionResearchEnum::SENSORS)
            ->setDataCommodity($chip)->setDuration(345600)->setDataAmount(1)->setFailureChance(10);
        $cost = new SpecialResearchCost()->setDefinition($foundation)->setCommodity($nanites)->setAmount(5);
        $foundation->getCosts()->add($cost);
        $this->em->persist($foundation);
        $this->em->persist($cost);
        $this->em->persist(new SpecialResearch()->setUser($this->colony->getUser())->setColony($this->colony)
            ->setResearchType(AwayMissionResearchEnum::SENSORS)->setStatus(SpecialResearchStatusEnum::COMPLETED)
            ->setStartTime(1)->setFinishTime(2)->setFailureChance(10));
        $this->recipe(1, TechnologyRecipeKindEnum::NANITE_RESEARCH, 172800, [76 => 10, 23 => 60, 33 => 20, 31 => 20, 3 => 50])->setFailureChance(35);
        $this->recipe(3, TechnologyRecipeKindEnum::DATASET_COPY, 43200, [31 => 2, 33 => 1])
            ->setResearchType(AwayMissionResearchEnum::SENSORS)->setOutputCommodity($chip)->setOutputAmount(1)->setEnergyCost(5)->setSettings(['max_concurrent_per_user' => 1]);
        $this->recipe(11, TechnologyRecipeKindEnum::MODULE_OPTIMIZATION, 57600, [])->setNaniteCommodity($nanites)->setSettings([
            'min_modules' => 5, 'max_modules' => 50, 'min_nanites' => 10, 'max_nanites' => 100,
            'base_success' => 20, 'max_base_success' => 50, 'extra_success' => 30, 'max_steps' => 70, 'accident_chance' => 0, 'data_amount' => 1
        ]);
    }

    /** @param array<int, int> $costs */
    private function recipe(int $id, TechnologyRecipeKindEnum $kind, int $duration, array $costs): TechnologyRecipe
    {
        $recipe = new TechnologyRecipe()->setId($id)->setKind($kind)->setName($kind->value)->setDuration($duration);
        $this->em->persist($recipe);
        foreach ($costs as $commodityId => $amount) {
            $this->em->persist(new TechnologyRecipeCost()->setRecipe($recipe)->setCommodity($this->commodity($commodityId))->setAmount($amount));
        }
        return $recipe;
    }

    private function commodity(int $id): Commodity
    {
        $commodity = $this->em->find(Commodity::class, $id);
        if ($commodity !== null) {
            return $commodity;
        }
        $metadata = $this->em->getClassMetadata(Commodity::class);
        $type = $metadata->generatorType;
        $generator = $metadata->idGenerator;
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);
        $metadata->setIdGenerator(new AssignedGenerator());
        try {
            $commodity = new Commodity()->setName('Testware ' . $id);
            $metadata->setFieldValue($commodity, 'id', $id);
            $this->em->persist($commodity);
            $this->em->flush();
            return $commodity;
        } finally {
            $metadata->setIdGeneratorType($type);
            $metadata->setIdGenerator($generator);
        }
    }

    /** @param array<int, int> $amounts */
    private function supply(array $amounts): void
    {
        foreach ($amounts as $id => $amount) {
            $this->getContainer()->get(StorageManagerInterface::class)->upperStorage($this->colony, $this->commodity($id), $amount);
        }
        $this->em->flush();
    }

    private function start(int $recipe, ?Module $module = null, int $modules = 0, int $nanites = 0): bool
    {
        return $this->em->wrapInTransaction(fn (): bool => $this->service->start($this->colony, $this->field, $this->repository->getRecipe($recipe), new InformationWrapper(), $module, $modules, $nanites));
    }

    private function research(): TechnologyResearch
    {
        return $this->repository->getResearchByUser($this->colony->getUser())[0];
    }

    private function process(): void
    {
        $this->em->flush();
        $this->em->wrapInTransaction(fn () => $this->getContainer()->get(FinishTechnologyJobs::class)->work());
    }

    private function unlockProduction(): void
    {
        $job = $this->getContainer()->get(TechnologyJobFactory::class)->research($this->colony, $this->field, $this->repository->getRecipe(1), null, AwayMissionResearchEnum::SENSORS, 6500, 0);
        $job->setStatus(SpecialResearchStatusEnum::COMPLETED);
        $this->em->persist($job);
        $this->em->flush();
    }

    public function testNaniteResearchConsumesMaterialsLastsTwoDaysAndUnlocksOnlyOnce(): void
    {
        $this->supply([76 => 20, 23 => 120, 33 => 40, 31 => 40, 3 => 100]);
        $nanites = $this->colony->getStorage()->get(76)->getAmount();
        self::assertTrue($this->start(1));
        $job = $this->research();
        self::assertSame(172800, $job->getFinishTime() - $job->getStartTime());
        self::assertSame(6500, $job->getSuccessChance());
        self::assertSame($this->field->getId(), $job->getFieldId());
        self::assertSame($nanites - 10, $this->colony->getStorage()->get(76)->getAmount());
        self::assertFalse($this->start(1));
        $this->process();
        self::assertSame(SpecialResearchStatusEnum::RUNNING, $job->getStatus());
        $job->setFinishTime(0);
        $this->process();
        self::assertSame(SpecialResearchStatusEnum::COMPLETED, $job->getStatus());
        self::assertSame($this->getContainer()->get(StuTime::class)->time(), $job->getFinishTime());
        self::assertTrue($this->repository->hasNaniteFabrication($this->colony->getUser()));
        self::assertFalse($this->start(1));
    }

    public function testFailedResearchKeepsMaterialsConsumedAndDoesNotUnlockProduction(): void
    {
        $this->supply([76 => 10, 23 => 60, 33 => 20, 31 => 20, 3 => 50]);
        self::assertTrue($this->start(1));
        $job = $this->research()->setSuccessChance(0)->setFinishTime(0);
        $before = $this->colony->getStorageSum();
        $this->process();
        self::assertSame(SpecialResearchStatusEnum::FAILED, $job->getStatus());
        self::assertSame($before, $this->colony->getStorageSum());
        self::assertFalse($this->repository->hasNaniteFabrication($this->colony->getUser()));
    }

    public function testCopyWaitsTwelveHoursAndFullStorageWithoutDuplicatingOutput(): void
    {
        $this->supply([31 => 10, 33 => 10]);
        self::assertTrue($this->start(3));
        self::assertFalse($this->start(3));
        $job = $this->repository->getProductionByUser($this->colony->getUser())[0];
        self::assertSame(43200, $job->getFinishTime() - $job->getStartTime());
        $this->process();
        self::assertSame(SpecialResearchStatusEnum::RUNNING, $job->getStatus());
        $job->setFinishTime(0);
        $this->colony->getChangeable()->setMaxStorage($this->colony->getStorageSum());
        $this->process();
        self::assertSame(SpecialResearchStatusEnum::RUNNING, $job->getStatus());
        $before = $this->colony->getStorage()->get(91)?->getAmount() ?? 0;
        $this->colony->getChangeable()->setMaxStorage(10000);
        $this->process();
        self::assertSame(SpecialResearchStatusEnum::COMPLETED, $job->getStatus());
        self::assertSame($before + 1, $this->colony->getStorage()->get(91)->getAmount());
        $this->process();
        self::assertSame($before + 1, $this->colony->getStorage()->get(91)->getAmount());
    }

    public function testNanitesAreProducedOnlyAtTheirColonyTick(): void
    {
        $this->unlockProduction();
        $this->supply([23 => 40, 3 => 20, 31 => 5, 29 => 10, 33 => 5]);
        $chipsBefore = $this->colony->getStorage()->get(31)->getAmount();
        $moduleId = $this->naniteModule->getId();
        $queues = $this->getContainer()->get(ModuleQueueRepositoryInterface::class);
        $this->moduleAction(false, ['func' => BuildingFunctionEnum::TECH_CENTER->value, 'moduleids' => [$moduleId], 'values' => [5]]);
        self::assertSame($chipsBefore - 5, $this->colony->getStorage()->get(31)?->getAmount() ?? 0);
        self::assertSame(5, $queues->getAmountByColonyAndModule($this->colony->getId(), $moduleId));
        $before = $this->colony->getStorage()->get(76)?->getAmount() ?? 0;
        $this->process();
        $this->em->wrapInTransaction(fn () => $this->getContainer()->get(SpacecraftTickManagerInterface::class)->work(false));
        self::assertSame(5, $queues->getAmountByColonyAndModule($this->colony->getId(), $moduleId));
        self::assertSame($before, $this->colony->getStorage()->get(76)?->getAmount() ?? 0);
        $colonyTick = $this->getContainer()->get(ColonyTickInterface::class);
        $otherColony = $this->em->find(Colony::class, 76754);
        self::assertNotNull($otherColony);
        $this->em->wrapInTransaction(fn () => $colonyTick->work($otherColony));
        self::assertSame(5, $queues->getAmountByColonyAndModule($this->colony->getId(), $moduleId));
        $this->em->wrapInTransaction(fn () => $colonyTick->work($this->colony));
        self::assertSame(0, $queues->getAmountByColonyAndModule($this->colony->getId(), $moduleId));
        self::assertSame($before + 5, $this->colony->getStorage()->get(76)?->getAmount());
    }

    public function testOptimizationImprovesOnlyOnSuccessAndRetainsProgressAfterFailure(): void
    {
        $this->unlockProduction();
        $this->supply([10601 => 100, 76 => 200, 91 => 5]);
        $module = $this->em->find(Module::class, 10601);
        $this->em->persist(new Researched()->setUser($this->colony->getUser())->setResearch($this->em->find(Research::class, 1001))->setFinished(1));
        $this->em->flush();
        self::assertTrue($this->start(11, $module, 5, 10));
        $job = $this->research();
        self::assertSame(2000, $job->getSuccessChance());
        self::assertSame(57600, $job->getFinishTime() - $job->getStartTime());
        $job->setFinishTime(0);
        $this->process();
        $progress = $this->getContainer()->get(ModuleOptimizationProgress::class);
        self::assertSame($this->getContainer()->get(StuTime::class)->time(), $job->getFinishTime());
        self::assertSame(1, $progress->getImprovementSteps($this->colony->getUser(), $module));
        self::assertTrue($this->start(11, $module, 50, 100));
        $second = $this->research();
        self::assertSame(5100, $second->getSuccessChance());
        $second->setSuccessChance(0)->setFinishTime(0);
        $this->process();
        self::assertSame(SpecialResearchStatusEnum::FAILED, $second->getStatus());
        self::assertSame(1, $progress->getImprovementSteps($this->colony->getUser(), $module));
        self::assertSame(0, $progress->getImprovementSteps($this->colony->getUser(), $this->em->find(Module::class, 10602)));
    }

    public function testBuildingRemovalAbortsResearchAndProductionImmediately(): void
    {
        $this->supply([76 => 10, 23 => 60, 33 => 25, 31 => 25, 3 => 50]);
        self::assertTrue($this->start(1));
        self::assertTrue($this->start(3));
        $job = $this->research();
        $production = $this->repository->getProductionByUser($this->colony->getUser())[0];
        $this->getContainer()->get(SpecialResearchCompletion::class)->buildingRemoved($this->field);
        self::assertSame(SpecialResearchStatusEnum::FAILED, $job->getStatus());
        self::assertSame(SpecialResearchStatusEnum::FAILED, $production->getStatus());
    }

    private function prepareModuleProduction(int $steps): Module
    {
        $function = BuildingFunctionEnum::MODULEFAB_TYPE3_LVL1;
        $building = $this->field->getBuilding();
        if (!$building->getFunctions()->containsKey($function->value)) {
            $entry = new BuildingFunction()->setBuildingId($building->getId())->setFunction($function);
            $this->em->getClassMetadata(BuildingFunction::class)->setFieldValue($entry, 'building', $building);
            $building->getFunctions()->set($function->value, $entry);
            $this->em->persist($entry);
        }
        $module = $this->em->find(Module::class, 10601);
        if ($this->em->getRepository(ModuleBuildingFunction::class)->findOneBy(['module_id' => $module->getId(), 'buildingfunction' => $function->value]) === null) {
            $entry = new ModuleBuildingFunction()->setModuleId($module->getId())->setBuildingFunction($function->value);
            $this->em->getClassMetadata(ModuleBuildingFunction::class)->setFieldValue($entry, 'module', $module);
            $this->em->persist($entry);
        }
        $this->em->persist(new Researched()->setUser($this->colony->getUser())->setResearch($this->em->find(Research::class, 1001))->setFinished(1));
        $this->em->persist(new UserModuleOptimization()->setUser($this->colony->getUser())->setModule($module)->setImprovementSteps($steps));
        $costs = [];
        foreach ($module->getCost() as $cost) {
            $costs[$cost->getCommodity()->getId()] = $cost->getAmount() * 10;
        }
        $this->supply($costs);
        return $module;
    }

    /** @param array<string, mixed> $parameters */
    private function moduleAction(bool $cancel, array $parameters): InformationWrapper
    {
        $variables = $parameters + ['id' => $this->colony->getId(), 'func' => BuildingFunctionEnum::MODULEFAB_TYPE3_LVL1->value];
        $previousGet = $_GET;
        $previousPost = $_POST;
        request::setMockVars(null);
        $_GET = $cancel ? [] : $variables;
        $_POST = $cancel ? $variables : [];
        $information = new InformationWrapper();
        $context = $this->mock(ActionControllerContext::class);
        $context->shouldReceive('getUser')->andReturn($this->colony->getUser());
        $context->shouldReceive('getInfo')->andReturn($information);
        $context->shouldReceive('setView')->once();
        $context->shouldReceive('setViewContext')->zeroOrMoreTimes();
        try {
            $this->em->wrapInTransaction(fn () => $this->getContainer()->get($cancel ? CancelModuleCreation::class : CreateModules::class)->handle($context));
        } finally {
            $_GET = $previousGet;
            $_POST = $previousPost;
        }
        return $information;
    }

    public function testStandardAndImprovedModulesHaveSeparateQueuesAndRefundTheirOwnNaniteCosts(): void
    {
        $module = $this->prepareModuleProduction(25);
        $this->supply([76 => 20]);
        $nanites = $this->colony->getStorage()->get(76)->getAmount();
        $repository = $this->getContainer()->get(ModuleQueueRepositoryInterface::class);
        $this->moduleAction(false, ['moduleids' => [$module->getId()], 'values' => [2]]);
        self::assertSame($nanites, $this->colony->getStorage()->get(76)->getAmount());
        $this->moduleAction(false, ['moduleids' => [$module->getId()], 'values' => [3], 'optimizations' => [$module->getId() => 1]]);
        self::assertSame($nanites - 9, $this->colony->getStorage()->get(76)->getAmount());
        self::assertSame(2, $repository->getByColonyAndModuleAndBuilding($this->colony->getId(), $module->getId(), BuildingFunctionEnum::MODULEFAB_TYPE3_LVL1->value, 0)->getAmount());
        self::assertSame(3, $repository->getByColonyAndModuleAndBuilding($this->colony->getId(), $module->getId(), BuildingFunctionEnum::MODULEFAB_TYPE3_LVL1->value, 25)->getAmount());
        $progress = $this->em->getRepository(UserModuleOptimization::class)->findOneBy(['user' => $this->colony->getUser(), 'module' => $module]);
        $progress->setImprovementSteps(70);
        $this->em->flush();
        $this->moduleAction(true, ['module' => $module->getId(), 'count' => 3]);
        self::assertSame($nanites - 6, $this->colony->getStorage()->get(76)->getAmount());
        self::assertNull($repository->getByColonyAndModuleAndBuilding($this->colony->getId(), $module->getId(), BuildingFunctionEnum::MODULEFAB_TYPE3_LVL1->value, 0));
        self::assertSame(2, $repository->getByColonyAndModuleAndBuilding($this->colony->getId(), $module->getId(), BuildingFunctionEnum::MODULEFAB_TYPE3_LVL1->value, 25)->getAmount());
        $this->moduleAction(true, ['module' => $module->getId(), 'count' => 10]);
        self::assertSame($nanites, $this->colony->getStorage()->get(76)->getAmount());
        self::assertNull($repository->getByColonyAndModuleAndBuilding($this->colony->getId(), $module->getId(), BuildingFunctionEnum::MODULEFAB_TYPE3_LVL1->value));
    }

    public function testNaniteStockLimitsImprovedProductionWithoutChangingItsBonus(): void
    {
        $module = $this->prepareModuleProduction(15);
        $this->supply([76 => 5]);
        $information = $this->moduleAction(false, ['moduleids' => [$module->getId()], 'values' => [3], 'optimizations' => [$module->getId() => 1]]);
        $queue = $this->getContainer()->get(ModuleQueueRepositoryInterface::class)->getByColonyAndModuleAndBuilding($this->colony->getId(), $module->getId(), BuildingFunctionEnum::MODULEFAB_TYPE3_LVL1->value, 15);
        self::assertNotNull($queue);
        self::assertSame(2, $queue->getAmount());
        self::assertSame(15, $queue->getImprovementSteps());
        self::assertSame(1, $this->colony->getStorage()->get(76)->getAmount());
        self::assertStringContainsString('benötigt: 1 Testware 76', $information->getInformationsAsString());
    }

    public function testTechnologyOverviewRendersCostsDurationAndOptimizationControls(): void
    {
        $this->supply([76 => 20, 23 => 120, 33 => 40, 31 => 40, 3 => 100, 10601 => 5, 91 => 2]);
        $variables = ['HOST' => $this->colony, 'SESSIONSTRING' => 'session'];
        $template = $this->mock(TemplateInterface::class);
        $template->shouldReceive('setTemplateVar')->andReturnUsing(static function (string $key, mixed $value) use (&$variables): void {
            $variables[$key] = $value;
        });
        $this->getContainer()->get(SpecialResearchProvider::class)->setTemplateVariables($this->colony, $template, $this->colony->getUser());
        $twig = new Environment($this->getContainer()->get(Environment::class)->getLoader(), ['cache' => false]);
        $this->getContainer()->make(TwigHelper::class, ['environment' => $twig])->registerFiltersAndFunctions();
        $html = $twig->render('html/colony/menu/specialResearch.twig', $variables);
        self::assertStringContainsString('2 Tage', $html);
        self::assertStringContainsString('35%', $html);
        self::assertStringNotContainsString('Nanitenfertigung: erfolgreich erforscht', $html);
        self::assertStringContainsString('name="B_START_TECHNOLOGY"', $html);
        self::assertStringContainsString('<td><img src="/assets/commodities/76.png"', $html);
        self::assertStringContainsString('<td><img src="/assets/buttons/e_trans2.png" title="Energie" /> Energie</td>', $html);
        $this->unlockProduction();
        $this->em->persist(new Researched()->setUser($this->colony->getUser())->setResearch($this->em->find(Research::class, 1001))->setFinished(1));
        $this->em->flush();
        $this->getContainer()->get(SpecialResearchProvider::class)->setTemplateVariables($this->colony, $template, $this->colony->getUser());
        $html = $twig->render('html/colony/menu/specialResearch.twig', $variables);
        self::assertStringNotContainsString('nanite_production', $html);
        self::assertStringNotContainsString('Produktionstick', $html);
        self::assertStringContainsString('name="module_count" min="5" max="50"', $html);
        self::assertStringContainsString('name="nanite_count" min="10" max="100"', $html);
        self::assertStringContainsString('Fertigungsbonus +0,0%', $html);
        self::assertStringContainsString('<td><img src="/assets/commodities/10601.png"', $html);
        self::assertStringContainsString('<td><img src="/assets/commodities/91.png"', $html);
        self::assertStringContainsString('<td><img src="/assets/commodities/76.png"', $html);
        $classSix = array_find($variables['OPTIMIZATION_MODULES'], static fn (array $row): bool => $row['module']->getId() === 10606);
        self::assertNotNull($classSix);
        self::assertSame(0, $classSix['stored']);
        self::assertTrue($classSix['canOptimize']);
        self::assertFalse($classSix['canPayMinimum']);
        self::assertStringContainsString($classSix['module']->getName(), $html);
        self::assertStringContainsString('<summary>Abgeschlossene Forschungen (2)</summary>', $html);
        self::assertGreaterThan(strpos($html, 'Moduloptimierung'), strpos($html, '<summary>Abgeschlossene Forschungen'));
        self::assertSame(['Grundlagenforschung', 'Nanitenfertigung'], array_column($variables['SPECIAL_RESEARCH_HISTORY'], 'research'));
    }

    public function testNanitesCanBeStartedAndViewedInTechnologyCenterWithoutSpecialResearchFunction(): void
    {
        $this->unlockProduction();
        $this->supply([23 => 80, 3 => 40, 31 => 10, 33 => 10]);
        $building = $this->field->getBuilding();
        self::assertNotNull($building);
        $researchFunction = $building->getFunctions()->remove(BuildingFunctionEnum::SPECIAL_RESEARCH->value);
        self::assertNotNull($researchFunction);
        $this->em->remove($researchFunction);
        $this->em->flush();
        $function = $building->getFunctions()->get(BuildingFunctionEnum::TECH_CENTER->value);
        self::assertNotNull($function);
        $variables = ['SESSIONSTRING' => 'session'];
        $view = $this->mock(ViewControllerContext::class);
        $view->shouldReceive('getUser')->andReturn($this->colony->getUser());
        $view->shouldReceive('showMacro')->with('html/colony/menu/componentFab.twig')->twice();
        $view->shouldReceive('addExecuteJS')->twice();
        $view->shouldReceive('setTemplateVar')->andReturnUsing(static function (string $key, mixed $value) use (&$variables): void {
            $variables[$key] = $value;
        });
        $moduleId = $this->naniteModule->getId();
        request::setMockVars(['id' => $this->colony->getId(), 'func' => $function->getId()]);
        $this->getContainer()->get(ShowModuleFab::class)->handle($view);
        $twig = new Environment($this->getContainer()->get(Environment::class)->getLoader(), ['cache' => false, 'strict_variables' => true]);
        $this->getContainer()->make(TwigHelper::class, ['environment' => $twig])->registerFiltersAndFunctions();
        $html = $twig->render('html/colony/menu/componentFab.twig', $variables);
        self::assertStringContainsString('Komponentenbaumenü', $html);
        self::assertStringContainsString('data-module-id="' . $moduleId . '"', $html);
        self::assertStringNotContainsString('B_START_TECHNOLOGY', $html);
        self::assertStringContainsString('startModuleProduction()', $html);
        self::assertStringContainsString('data-energy-cost="10"', $html);
        self::assertStringContainsString('data-cost="8"', $html);

        $information = new InformationWrapper();
        $action = $this->mock(ActionControllerContext::class);
        $action->shouldReceive('getUser')->andReturn($this->colony->getUser());
        $action->shouldReceive('getInfo')->andReturn($information);
        $action->shouldReceive('setView')->with(ShowColony::VIEW_IDENTIFIER)->once();
        request::setMockVars(['id' => $this->colony->getId(), 'moduleids' => [$moduleId], 'values' => [7], 'func' => BuildingFunctionEnum::TECH_CENTER->value]);
        $this->em->wrapInTransaction(fn () => $this->getContainer()->get(CreateModules::class)->handle($action));
        self::assertSame(7, $this->getContainer()->get(ModuleQueueRepositoryInterface::class)->getAmountByColonyAndModule($this->colony->getId(), $moduleId));
        self::assertStringContainsString('7 Testware 76', $information->getInformationsAsString());
        request::setMockVars(['id' => $this->colony->getId(), 'func' => $function->getId()]);

        $this->getContainer()->get(ShowModuleFab::class)->handle($view);
        $html = $twig->render('html/colony/menu/componentFab.twig', $variables);
        self::assertStringContainsString('id="module_' . $moduleId . '_count" value="7"', $html);
        self::assertStringContainsString('cancelModuleQueueEntries(' . $moduleId . ')', $html);
    }

    private function seedNaniteModule(): Module
    {
        $module = new Module()->setName('Testware 76')->setCommodity($this->commodity(76))
            ->setType(SpacecraftModuleTypeEnum::SPECIAL)->setLevel(0)->setEcost(10)->setResearch(null);
        $this->em->persist($module);
        $this->em->flush();
        foreach ([23 => 8, 3 => 4, 31 => 1, 33 => 1] as $commodityId => $amount) {
            $cost = new ModuleCost()->setModule($module)->setCommodity($this->commodity($commodityId))->setAmount($amount);
            $module->getCost()->add($cost);
            $this->em->persist($cost);
        }
        $this->em->persist(new ModuleBuildingFunction()->setModule($module)->setBuildingFunction(BuildingFunctionEnum::TECH_CENTER->value));
        $this->em->flush();
        return $module;
    }

    public function testNaniteQueueCanBeExtendedAndCancelledWithMaterialRefunds(): void
    {
        $this->unlockProduction();
        $this->supply([23 => 160, 3 => 80, 31 => 20, 33 => 20]);
        $moduleId = $this->naniteModule->getId();
        $queues = $this->getContainer()->get(ModuleQueueRepositoryInterface::class);
        $material = $this->colony->getStorage()->get(23);
        self::assertNotNull($material);
        $initialMaterial = $material->getAmount();
        $this->moduleAction(false, ['func' => BuildingFunctionEnum::TECH_CENTER->value, 'moduleids' => [$moduleId], 'values' => [5]]);
        $this->moduleAction(false, ['func' => BuildingFunctionEnum::TECH_CENTER->value, 'moduleids' => [$moduleId], 'values' => [7]]);
        self::assertSame(12, $queues->getAmountByColonyAndModule($this->colony->getId(), $moduleId));
        self::assertCount(1, $queues->getByColonyAndBuilding($this->colony->getId(), [BuildingFunctionEnum::TECH_CENTER->value]));
        self::assertSame($initialMaterial - 96, $material->getAmount());
        $this->moduleAction(true, ['func' => BuildingFunctionEnum::TECH_CENTER->value, 'module' => $moduleId, 'count' => 3]);
        self::assertSame(9, $queues->getAmountByColonyAndModule($this->colony->getId(), $moduleId));
        self::assertSame($initialMaterial - 72, $material->getAmount());
        $before = $this->colony->getStorage()->get(76)?->getAmount() ?? 0;
        $this->em->wrapInTransaction(fn () => $this->getContainer()->get(ColonyTickInterface::class)->work($this->colony));
        self::assertSame($before + 9, $this->colony->getStorage()->get(76)?->getAmount());
        self::assertSame(0, $queues->getAmountByColonyAndModule($this->colony->getId(), $moduleId));
    }

    public function testNaniteProductionRequiresCompletedResearch(): void
    {
        $this->supply([23 => 40, 3 => 20, 31 => 5, 33 => 5]);
        $module = $this->naniteModule;
        $this->moduleAction(false, ['func' => BuildingFunctionEnum::TECH_CENTER->value, 'moduleids' => [$module->getId()], 'values' => [5]]);
        self::assertSame(0, $this->getContainer()->get(ModuleQueueRepositoryInterface::class)->getAmountByColonyAndModule($this->colony->getId(), $module->getId()));
    }
}
