<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\SpecialResearch;
use Stu\Orm\Entity\SpecialResearchCost;
use Stu\Orm\Entity\SpecialResearchDefinition;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Entity\TechnologyRecipeCost;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\SpecialResearchDefinitionRepositoryInterface;
use Stu\Orm\Repository\SpecialResearchRepositoryInterface;
use Stu\Orm\Repository\TechnologyRepositoryInterface;
use Stu\Orm\Repository\UserModuleOptimizationRepositoryInterface;
use Stu\StuTestCase;

final class TorpedoResearchTest extends StuTestCase
{
    private TechnologyRepositoryInterface&MockInterface $recipes;
    private SpecialResearchRepositoryInterface&MockInterface $foundations;
    private ResearchedRepositoryInterface&MockInterface $researched;
    private User&MockInterface $user;
    private TechnologyCatalog $catalog;
    private int $factionId = 3;
    private bool $moduleResearched = true;
    /** @var list<SpecialResearch> */
    private array $completed = [];

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->recipes = $this->mock(TechnologyRepositoryInterface::class);
        $this->foundations = $this->mock(SpecialResearchRepositoryInterface::class);
        $this->researched = $this->mock(ResearchedRepositoryInterface::class);
        $this->user = $this->mock(User::class);
        $this->user->shouldReceive('getFactionId')->andReturnUsing(fn (): int => $this->factionId);
        $this->foundations->shouldReceive('getByUser')->with($this->user)->andReturnUsing(fn (): array => $this->completed);
        $this->researched->shouldReceive('hasUserFinishedResearch')->andReturnUsing(fn (): bool => $this->moduleResearched);
        $this->completed = [new SpecialResearch()->setResearchType(AwayMissionResearchEnum::TORPEDO_WEAPONS)->setStatus(SpecialResearchStatusEnum::COMPLETED)];
        $this->catalog = new TechnologyCatalog($this->recipes, $this->foundations, $this->researched, new TechnologyRecipeValidator());
    }

    public function testResearchMetadataUsesTheNewDatasetAndACompleteNarrative(): void
    {
        $system = AwayMissionResearchEnum::from('torpedo_weapons');

        self::assertSame(AwayMissionResearchEnum::TORPEDO_WEAPONS, $system);
        self::assertSame('Torpedomodule', $system->getDescription());
        self::assertSame(94, $system->getCommodityId());
        self::assertSame('die verbesserten Torpedomodule', $system->getImprovedSystem());
        self::assertStringContainsString('Torpedorampen', $system->getFinding());
        self::assertStringContainsString('Schilde', $system->getFinding());
        self::assertStringEndsNotWith('.', $system->getFinding());
    }

    /** @return iterable<string, array{int, int, ?int}> */
    public static function availableModules(): iterable
    {
        foreach (range(1, 6) as $level) {
            foreach (range(1, 5) as $faction) {
                yield sprintf('standard class %d for faction %d', $level, $faction) => [$level, $faction, null];
            }
            yield sprintf('own klingon launcher class %d', $level) => [$level, 3, 3];
            yield sprintf('own cardassian launcher class %d', $level) => [$level, 4, 4];
        }
    }

    #[DataProvider('availableModules')]
    public function testTheTorpedoFoundationUnlocksEveryStandardAndOwnModuleClass(int $level, int $userFaction, ?int $moduleFaction): void
    {
        $this->factionId = $userFaction;
        $module = $this->module($level, $moduleFaction);

        self::assertSame(AwayMissionResearchEnum::TORPEDO_WEAPONS, $this->catalog->getSystem($module));
        self::assertTrue($this->catalog->supportsOptimization($this->user, $module));
        self::assertTrue($this->catalog->canOptimize($this->user, $module));
    }

    /** @return iterable<string, array{string}> */
    public static function unavailableModules(): iterable
    {
        foreach (['no foundation', 'running foundation', 'failed foundation', 'foreign klingon', 'foreign cardassian', 'npc', 'class zero', 'class seven', 'module not researched'] as $state) {
            yield $state => [$state];
        }
    }

    #[DataProvider('unavailableModules')]
    public function testTorpedoOptimizationKeepsAllFoundationFactionAndResearchRestrictions(string $state): void
    {
        $module = $this->module();
        if ($state === 'no foundation') {
            $this->completed = [];
        } elseif ($state === 'running foundation' || $state === 'failed foundation') {
            $this->completed[0]->setStatus($state === 'running foundation' ? SpecialResearchStatusEnum::RUNNING : SpecialResearchStatusEnum::FAILED);
        } elseif ($state === 'foreign klingon' || $state === 'foreign cardassian') {
            $this->factionId = 1;
            $module = $this->module(factionId: $state === 'foreign klingon' ? 3 : 4);
        } elseif ($state === 'npc') {
            new ReflectionProperty(Module::class, 'is_npc')->setValue($module, true);
        } elseif ($state === 'class zero' || $state === 'class seven') {
            $module->setLevel($state === 'class zero' ? 0 : 7);
        } else {
            $this->moduleResearched = false;
        }

        self::assertFalse($this->catalog->canOptimize($this->user, $module));
    }

    public function testTheEnergyWeaponFoundationCannotReplaceTheTorpedoFoundation(): void
    {
        $this->completed = [new SpecialResearch()->setResearchType(AwayMissionResearchEnum::ENERGY_WEAPONS)->setStatus(SpecialResearchStatusEnum::COMPLETED)];

        self::assertFalse($this->catalog->supportsOptimization($this->user, $this->module()));
        self::assertFalse($this->catalog->canUse($this->user, $this->copyRecipe()));
    }

    public function testTheTorpedoFoundationUnlocksCopyingAndTheNaniteResearch(): void
    {
        $copy = $this->copyRecipe();
        $this->recipes->shouldReceive('getRecipes')->once()->andReturn([$copy]);
        $this->recipes->shouldReceive('hasNaniteFabrication')->once()->with($this->user)->andReturn(false);
        $naniteResearch = new TechnologyRecipe()->setKind(TechnologyRecipeKindEnum::NANITE_RESEARCH)->setDuration(172800)->setFailureChance(35);

        self::assertSame([$copy], $this->catalog->getRecipes());
        self::assertTrue($this->catalog->canUse($this->user, $copy));
        self::assertTrue($this->catalog->canUse($this->user, $naniteResearch));
    }

    public function testTheFoundationCatalogIncludesTheTorpedoDatasetAndConfiguredModuleCosts(): void
    {
        $definition = $this->definition();
        $repository = $this->mock(SpecialResearchDefinitionRepositoryInterface::class);
        $repository->shouldReceive('getByFaction')->once()->with(3)->andReturn([$definition]);
        $catalog = new SpecialResearchCatalog($repository);

        self::assertSame($definition, $catalog->getDefinition(AwayMissionResearchEnum::TORPEDO_WEAPONS, 3));
        self::assertSame([94 => 1, 76 => 5, 10801 => 20, 10802 => 10], $catalog->getCosts($definition));
        self::assertSame(345600, $definition->getDuration());
        self::assertSame(10, $definition->getFailureChance());
        self::assertSame(['damage_percent' => 15, 'worker_min_percent' => 25, 'worker_max_percent' => 75, 'population_min' => 3, 'population_max' => 12], $definition->getFailureSettings());
    }

    public function testTorpedoOptimizationConsumesTheTorpedoDatasetAndTheSelectedModule(): void
    {
        $module = $this->module(factionId: 3);
        $recipe = new TechnologyRecipe()->setKind(TechnologyRecipeKindEnum::MODULE_OPTIMIZATION)->setDuration(57600)->setNaniteCommodity($this->commodity(76))->setSettings([
            'min_modules' => 5, 'max_modules' => 50, 'min_nanites' => 10, 'max_nanites' => 100,
            'base_success' => 20, 'max_base_success' => 50, 'extra_success' => 30, 'max_steps' => 70, 'accident_chance' => 5, 'data_amount' => 1
        ]);
        $this->recipes->shouldReceive('getCosts')->once()->with($recipe)->andReturn([]);
        $definition = $this->definition();
        $definitions = $this->mock(SpecialResearchDefinitionRepositoryInterface::class);
        $definitions->shouldReceive('getByFaction')->once()->with(3)->andReturn([$definition]);

        $order = $this->orders(new SpecialResearchCatalog($definitions))->build($this->user, $recipe, $module, 5, 10, new InformationWrapper());

        self::assertNotNull($order);
        self::assertSame(AwayMissionResearchEnum::TORPEDO_WEAPONS, $order->system);
        self::assertSame($module, $order->module);
        self::assertSame([$module->getCommodityId() => 5, 76 => 10, 94 => 1], $order->costs);
        self::assertSame(2000, $order->chance);
    }

    public function testTorpedoDatasetCopiesUseTheConfiguredCopyMaterialsAndResearchType(): void
    {
        $recipe = $this->copyRecipe();
        $this->recipes->shouldReceive('getCosts')->once()->with($recipe)->andReturn([
            new TechnologyRecipeCost()->setCommodity($this->commodity(31))->setAmount(2),
            new TechnologyRecipeCost()->setCommodity($this->commodity(33))->setAmount(1)
        ]);

        $order = $this->orders(new SpecialResearchCatalog($this->mock(SpecialResearchDefinitionRepositoryInterface::class)))->build($this->user, $recipe, null, 0, 0, new InformationWrapper());

        self::assertNotNull($order);
        self::assertSame(AwayMissionResearchEnum::TORPEDO_WEAPONS, $order->system);
        self::assertSame([31 => 2, 33 => 1], $order->costs);
        self::assertSame(43200, $recipe->getDuration());
        self::assertSame(5, $recipe->getEnergyCost());
        self::assertSame(94, $recipe->getOutputCommodity()->getId());
    }

    private function orders(SpecialResearchCatalog $definitions): TechnologyOrderBuilder
    {
        $progress = $this->mock(UserModuleOptimizationRepositoryInterface::class);
        $progress->shouldReceive('get')->andReturnNull();
        $materials = new TechnologyMaterials($this->recipes, $this->mock(CommodityRepositoryInterface::class), $this->mock(StorageManagerInterface::class));
        return new TechnologyOrderBuilder($materials, $this->catalog, $definitions, new ModuleOptimizationProgress($progress), new ModuleOptimizationChance());
    }

    private function definition(): SpecialResearchDefinition
    {
        $definition = new SpecialResearchDefinition()->setId(13)->setResearchType(AwayMissionResearchEnum::TORPEDO_WEAPONS)->setDataCommodity($this->commodity(94))->setDataAmount(1)->setDuration(345600)->setFailureChance(10);
        foreach ([76 => 5, 10801 => 20, 10802 => 10] as $id => $amount) {
            $definition->getCosts()->add(new SpecialResearchCost()->setDefinition($definition)->setCommodity($this->commodity($id))->setAmount($amount));
        }
        return $definition;
    }

    private function copyRecipe(): TechnologyRecipe
    {
        return new TechnologyRecipe()->setKind(TechnologyRecipeKindEnum::DATASET_COPY)->setResearchType(AwayMissionResearchEnum::TORPEDO_WEAPONS)->setDuration(43200)->setEnergyCost(5)->setOutputCommodity($this->commodity(94))->setOutputAmount(1)->setSettings(['max_concurrent_per_user' => 1]);
    }

    private function module(int $level = 1, ?int $factionId = null): Module
    {
        $module = new Module()->setType(SpacecraftModuleTypeEnum::TORPEDO)->setLevel($level)->setResearchId(10801)->setCommodityId(10801);
        if ($factionId !== null) {
            $faction = $this->mock(Faction::class);
            $faction->shouldReceive('getId')->andReturn($factionId);
            new ReflectionProperty(Module::class, 'faction')->setValue($module, $faction);
            $module->setCommodityId($factionId === 3 ? 10831 : 11841);
        }
        return $module;
    }

    private function commodity(int $id): Commodity
    {
        $commodity = new Commodity();
        new ReflectionProperty(Commodity::class, 'id')->setValue($commodity, $id);
        return $commodity;
    }
}
