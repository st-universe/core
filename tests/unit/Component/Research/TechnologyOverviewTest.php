<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Entity\Faction;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\SpecialResearch;
use Stu\Orm\Entity\SpecialResearchCost;
use Stu\Orm\Entity\SpecialResearchDefinition;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Entity\TechnologyRecipeCost;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\Orm\Repository\SpecialResearchDefinitionRepositoryInterface;
use Stu\Orm\Repository\SpecialResearchRepositoryInterface;
use Stu\Orm\Repository\TechnologyRepositoryInterface;
use Stu\Orm\Repository\UserModuleOptimizationRepositoryInterface;
use Stu\StuTestCase;

final class TechnologyOverviewTest extends StuTestCase
{
    private MockInterface&TechnologyRepositoryInterface $jobs;
    private MockInterface&ModuleRepositoryInterface $modules;
    private MockInterface&ResearchedRepositoryInterface $researched;
    private MockInterface&SpecialResearchRepositoryInterface $foundations;
    private User $user;
    private MockInterface&Colony $colony;
    private ColonyChangeable $changeable;
    /** @var list<TechnologyRecipeCost> */
    private array $costs = [];
    private TechnologyRecipe $recipe;
    private TechnologyOverview $overview;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->jobs = $this->mock(TechnologyRepositoryInterface::class);
        $this->modules = $this->mock(ModuleRepositoryInterface::class);
        $this->researched = $this->mock(ResearchedRepositoryInterface::class);
        $this->foundations = $this->mock(SpecialResearchRepositoryInterface::class);
        $commodities = $this->mock(CommodityRepositoryInterface::class);
        $definitions = $this->mock(SpecialResearchDefinitionRepositoryInterface::class);
        $progress = $this->mock(UserModuleOptimizationRepositoryInterface::class);
        $progress->shouldReceive('get')->andReturnNull();
        $fields = $this->mock(PlanetFieldRepositoryInterface::class);
        $catalog = new TechnologyCatalog($this->jobs, $this->foundations, $this->researched, new TechnologyRecipeValidator());
        $materials = new TechnologyMaterials($this->jobs, $commodities, $this->mock(StorageManagerInterface::class));
        $this->overview = new TechnologyOverview($this->jobs, $catalog, $materials, new ModuleOptimizationProgress($progress), new TechnologyFacility(new SpecialResearchFacility($fields), $fields), $commodities, $this->modules, new SpecialResearchCatalog($definitions));
        $this->user = $this->mock(User::class);
        $this->user->shouldReceive('getFactionId')->andReturn(3);
        $this->colony = $this->mock(Colony::class);
        $this->colony->shouldReceive('getUser')->andReturn($this->user);
        $this->changeable = new ColonyChangeable($this->colony)->setEps(100);
        $this->colony->shouldReceive('getChangeable')->andReturn($this->changeable);
        $definition = new SpecialResearchDefinition()->setResearchType(AwayMissionResearchEnum::ENERGY_WEAPONS)
            ->setDataCommodity($this->commodity(77))->setDataAmount(1)->setDuration(345600)->setFailureChance(10);
        $definition->getCosts()->add(new SpecialResearchCost()->setDefinition($definition)->setCommodity($this->commodity(76))->setAmount(5));
        $definitions->shouldReceive('getByFaction')->with(3)->once()->andReturn([$definition]);
        $this->recipe = new TechnologyRecipe()->setKind(TechnologyRecipeKindEnum::MODULE_OPTIMIZATION)->setDuration(57600)->setEnergyCost(10)->setNaniteCommodity($this->commodity(76))->setSettings([
            'min_modules' => 5, 'max_modules' => 50, 'min_nanites' => 10, 'max_nanites' => 100,
            'base_success' => 20, 'max_base_success' => 50, 'extra_success' => 30, 'max_steps' => 70, 'accident_chance' => 0, 'data_amount' => 1
        ]);
        $this->jobs->shouldReceive('getRecipes')->once()->andReturn([$this->recipe]);
        $this->jobs->shouldReceive('hasNaniteFabrication')->with($this->user)->andReturn(true);
        $this->jobs->shouldReceive('hasRunningResearch')->with($this->user)->andReturn(false);
        $this->jobs->shouldReceive('getResearchByUser')->with($this->user)->once()->andReturn([]);
        $this->jobs->shouldReceive('getProductionByUser')->with($this->user)->once()->andReturn([]);
        $this->jobs->shouldReceive('getCosts')->with($this->recipe)->andReturnUsing(fn (): array => $this->costs);
        $this->foundations->shouldReceive('getByUser')->with($this->user)->andReturn([
            new SpecialResearch()->setResearchType(AwayMissionResearchEnum::ENERGY_WEAPONS)->setStatus(SpecialResearchStatusEnum::COMPLETED)
        ]);
    }

    public function testResearchedClassesOneThroughSixAppearEvenWithEmptyStorage(): void
    {
        $candidates = array_map(fn (int $level): Module => $this->module($level, $level), range(1, 6));
        $this->modules->shouldReceive('getStandardAndFactionByLevels')->with(3, range(1, 6))->once()->andReturn($candidates);
        $this->modules->shouldNotReceive('getByCommodityIds');
        $this->colony->shouldReceive('getStorage')->andReturn(new ArrayCollection());
        $this->researched->shouldReceive('hasUserFinishedResearch')->andReturn(true);

        $rows = $this->rows();

        self::assertSame($candidates, array_column($rows, 'module'));
        self::assertSame(array_fill(0, 6, 0), array_column($rows, 'stored'));
        self::assertSame(array_fill(0, 6, true), array_column($rows, 'canOptimize'));
        self::assertSame(array_fill(0, 6, false), array_column($rows, 'canPayMinimum'));
    }

    public function testUnresearchedOwnClassesRemainVisibleButLockedAndForeignOrNpcModulesStayHidden(): void
    {
        $neutral = $this->module(1, 1);
        $own = $this->module(2, 6, faction: 3);
        $foreign = $this->module(3, 2, faction: 2);
        $npc = $this->module(4, 3, npc: true);
        $unsupported = $this->module(5, 4, type: SpacecraftModuleTypeEnum::TORPEDO);
        $missingFoundation = $this->module(6, 5, type: SpacecraftModuleTypeEnum::SENSOR);
        $this->modules->shouldReceive('getStandardAndFactionByLevels')->with(3, range(1, 6))->once()->andReturn([$neutral, $own, $foreign, $npc, $unsupported, $missingFoundation]);
        $this->colony->shouldReceive('getStorage')->andReturn(new ArrayCollection());
        $this->researched->shouldReceive('hasUserFinishedResearch')->with($this->user, [1001])->once()->andReturn(true);
        $this->researched->shouldReceive('hasUserFinishedResearch')->with($this->user, [1002])->once()->andReturn(false);

        $rows = $this->rows();

        self::assertSame([$neutral, $own], array_column($rows, 'module'));
        self::assertSame([true, false], array_column($rows, 'canOptimize'));
        self::assertSame([0, 0], array_column($rows, 'stored'));
    }

    /** @return iterable<string, array{int, int, int, int, int, bool}> */
    public static function minimumAvailability(): iterable
    {
        yield 'all minimum inputs' => [5, 10, 1, 2, 10, true];
        yield 'missing module' => [4, 10, 1, 2, 10, false];
        yield 'missing nanite' => [5, 9, 1, 2, 10, false];
        yield 'missing dataset' => [5, 10, 0, 2, 10, false];
        yield 'missing recipe material' => [5, 10, 1, 1, 10, false];
        yield 'missing energy' => [5, 10, 1, 2, 9, false];
    }

    #[DataProvider('minimumAvailability')]
    public function testMinimumAvailabilityRequiresModulesNanitesDatasetRecipeMaterialsAndEnergy(int $modules, int $nanites, int $datasets, int $baseMaterial, int $energy, bool $expected): void
    {
        $module = $this->module(101, 1);
        $this->changeable->setEps($energy);
        $this->costs = [new TechnologyRecipeCost()->setRecipe($this->recipe)->setCommodity($this->commodity(88))->setAmount(2)];
        $this->modules->shouldReceive('getStandardAndFactionByLevels')->with(3, range(1, 6))->once()->andReturn([$module]);
        $this->researched->shouldReceive('hasUserFinishedResearch')->with($this->user, [1101])->once()->andReturn(true);
        $this->colony->shouldReceive('getStorage')->andReturn(new ArrayCollection([
            101 => new Storage()->setAmount($modules),
            76 => new Storage()->setAmount($nanites),
            77 => new Storage()->setAmount($datasets),
            88 => new Storage()->setAmount($baseMaterial)
        ]));

        $rows = $this->rows();

        self::assertSame([$expected], array_column($rows, 'canPayMinimum'));
        self::assertSame([$modules], array_column($rows, 'stored'));
    }

    /** @return list<array<string, mixed>> */
    private function rows(): array
    {
        $variables = [];
        $template = $this->mock(TemplateInterface::class);
        $template->shouldReceive('setTemplateVar')->andReturnUsing(static function (string $key, mixed $value) use (&$variables): void {
            $variables[$key] = $value;
        });
        $this->overview->setTemplateVariables($this->colony, null, $template, $this->user);
        self::assertIsArray($variables['OPTIMIZATION_MODULES']);
        return array_values($variables['OPTIMIZATION_MODULES']);
    }

    private function module(int $id, int $level, ?int $faction = null, bool $npc = false, SpacecraftModuleTypeEnum $type = SpacecraftModuleTypeEnum::PHASER): Module
    {
        $module = new Module()->setType($type)->setLevel($level)->setResearchId(1000 + $id)->setCommodityId($id);
        new ReflectionProperty(Module::class, 'id')->setValue($module, $id);
        new ReflectionProperty(Module::class, 'is_npc')->setValue($module, $npc);
        if ($faction !== null) {
            $race = $this->mock(Faction::class);
            $race->shouldReceive('getId')->andReturn($faction);
            new ReflectionProperty(Module::class, 'faction')->setValue($module, $race);
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
