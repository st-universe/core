<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\BuildplanModule;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ShipRumpModuleLevel;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftRumpBaseValues;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\StuTestCase;

final class ModuleSelectorEntryQualityTest extends StuTestCase
{
    /** @return iterable<string, array{int, int, int}> */
    public static function damageValues(): iterable
    {
        yield 'standard value' => [100, 0, 100];
        yield 'full seven percent' => [100, 70, 107];
        yield 'small fractional bonus stays whole' => [25, 4, 25];
        yield 'below half stays lower' => [25, 19, 25];
        yield 'half rounds upward' => [25, 20, 26];
        yield 'seven percent with fractional result' => [25, 70, 27];
    }

    #[DataProvider('damageValues')]
    public function testExplicitQualityPreviewUsesTheSameDamageCalculationAsInstallation(int $base, int $steps, int $expected): void
    {
        $module = $this->module(SpacecraftModuleTypeEnum::PHASER);
        $entry = $this->entry($module, $base);

        $preview = $entry->forQuality($steps);

        self::assertNotSame($entry, $preview);
        self::assertSame($steps, $preview->getSelectedQuality());
        self::assertSame($expected, $preview->getValue());
        self::assertSame($base, $entry->getValue());
        self::assertSame(0, $entry->getSelectedQuality());
        self::assertFalse($preview->isChosen());
    }

    public function testEpsAndBatteryBothUseTheExplicitPreviewQuality(): void
    {
        $entry = $this->entry($this->module(SpacecraftModuleTypeEnum::EPS), 95);
        $preview = $entry->forQuality(70);

        self::assertSame(102, $preview->getValue());
        self::assertSame(34, $preview->getSecondvalue());
        self::assertSame(95, $entry->getValue());
        self::assertSame(32, $entry->getSecondvalue());
    }

    /** @return iterable<string, array{?SpacecraftSystemTypeEnum, int}> */
    public static function reactorTypes(): iterable
    {
        yield 'warp reactor' => [null, 1605];
        yield 'fusion reactor' => [SpacecraftSystemTypeEnum::FUSION_REACTOR, 1070];
        yield 'singularity reactor' => [SpacecraftSystemTypeEnum::SINGULARITY_REACTOR, 2140];
    }

    #[DataProvider('reactorTypes')]
    public function testReactorOutputAndCapacityUseTheSameSelectedQuality(?SpacecraftSystemTypeEnum $type, int $expectedCapacity): void
    {
        $module = $this->module(SpacecraftModuleTypeEnum::REACTOR);
        new ReflectionProperty(Module::class, 'system_type')->setValue($module, $type);
        $entry = $this->entry($module, 100);
        $preview = $entry->forQuality(70);

        self::assertSame(107, $preview->getValue());
        self::assertSame($expectedCapacity, $preview->getSecondvalue());
        self::assertSame(100, $entry->getValue());
        self::assertSame(0, $entry->getSelectedQuality());
    }

    public function testSavedPlanValuesAndModuleEntitiesRemainUnchangedAcrossIndependentPreviews(): void
    {
        $module = $this->module(SpacecraftModuleTypeEnum::PHASER)->setDefaultFactor(20)->setUpgradeFactor(40)->setDowngradeFactor(20);
        $other = $this->module(SpacecraftModuleTypeEnum::EPS, 2);
        $plan = new SpacecraftBuildplan();
        $saved = new BuildplanModule()->setBuildplan($plan)->setModule($module)->setModuleType($module->getType())->setImprovementSteps(17);
        $savedOther = new BuildplanModule()->setBuildplan($plan)->setModule($other)->setModuleType($other->getType())->setImprovementSteps(33);
        $plan->getModules()->set(1, $saved);
        $plan->getModules()->set(2, $savedOther);
        $collection = $plan->getModules();
        $originalModules = $collection->toArray();
        $entry = $this->entry($module, 100, $plan);

        $standard = $entry->forQuality(0);
        $improved = $entry->forQuality(70);
        $secondPreview = $improved->forQuality(4);

        self::assertSame(120, $standard->getValue());
        self::assertSame(127, $improved->getValue());
        self::assertSame(120, $secondPreview->getValue());
        self::assertSame(4, $secondPreview->getSelectedQuality());
        self::assertSame(122, $entry->getValue());
        self::assertSame(17, $entry->getSelectedQuality());
        self::assertTrue($standard->isChosen());
        self::assertTrue($improved->isChosen());
        self::assertSame($module, $improved->getModule());
        self::assertSame($collection, $plan->getModules());
        self::assertSame($originalModules, $plan->getModules()->toArray());
        self::assertSame(17, $saved->getImprovementSteps());
        self::assertSame(33, $savedOther->getImprovementSteps());
        self::assertSame(20, $module->getDefaultFactor());
        self::assertSame(40, $module->getUpgradeFactor());
        self::assertSame(20, $module->getDowngradeFactor());
    }

    public function testVariantShapeAndCompactLabelsKeepTheActualStoredAmounts(): void
    {
        $entry = $this->entry($this->module(SpacecraftModuleTypeEnum::PHASER), 100);
        $preview = $entry->forQuality(4);

        self::assertSame([
            ['steps' => 0, 'amount' => 3, 'label' => 'Standard'],
            ['steps' => 4, 'amount' => 2, 'label' => '+0,4%']
        ], $preview->getQualityVariants());
        self::assertSame(5, $entry->getStoredAmount());
        self::assertSame(5, $preview->getStoredAmount());
    }

    /** @return iterable<array{int}> */
    public static function invalidQualities(): iterable
    {
        yield [-1];
        yield [71];
    }

    #[DataProvider('invalidQualities')]
    public function testInvalidPreviewQualitiesAreRejected(int $steps): void
    {
        $entry = $this->entry($this->module(SpacecraftModuleTypeEnum::PHASER), 100);
        $this->expectException(InvalidArgumentException::class);

        $entry->forQuality($steps);
    }

    private function module(SpacecraftModuleTypeEnum $type, int $id = 1): Module
    {
        $module = new Module()->setType($type)->setLevel(1)->setCommodityId($id);
        new ReflectionProperty(Module::class, 'id')->setValue($module, $id);
        return $module;
    }

    private function entry(Module $module, int $base, ?SpacecraftBuildplan $plan = null): ModuleSelectorEntry
    {
        $rump = $this->mock(SpacecraftRump::class);
        $rump->shouldReceive('getBaseValues')->andReturn(new SpacecraftRumpBaseValues()->setModuleLevel(1)->setBaseDamage($base)->setBaseEps($base)->setBaseReactor($base));
        $colony = $this->mock(Colony::class);
        $colony->shouldReceive('getStorage')->andReturn(new ArrayCollection([
            $module->getCommodityId() => new Storage()->setAmount(5)->addModuleQualityAmounts([4 => 2])
        ]));
        return new ModuleSelectorEntry(
            $this->mock(ModuleSelectorInterface::class),
            $module,
            $rump,
            new ShipRumpModuleLevel(),
            $colony,
            new User(),
            $this->mock(ModuleRepositoryInterface::class),
            $plan
        );
    }
}
