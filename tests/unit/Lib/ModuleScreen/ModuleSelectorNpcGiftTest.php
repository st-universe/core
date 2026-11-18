<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleScreen;

use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionProperty;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\BuildplanModule;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\ShipRumpModuleLevel;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\StuTestCase;

final class ModuleSelectorNpcGiftTest extends StuTestCase
{
    public function testNpcGiftPreservesModulesOutsideRegularRumpRestrictions(): void
    {
        $hull = $this->module(11125, SpacecraftModuleTypeEnum::HULL);
        $special = $this->module(19403, SpacecraftModuleTypeEnum::SPECIAL);
        $plan = new SpacecraftBuildplan()->setNpcGift(true);
        foreach ([$hull, $special] as $module) {
            $plan->getModules()->set($module->getId(), new BuildplanModule()
                ->setBuildplan($plan)->setModule($module)->setModuleType($module->getType()));
        }

        $moduleRepository = $this->mock(ModuleRepositoryInterface::class);
        $moduleRepository->shouldNotReceive('getByTypeColonyAndLevel');
        $moduleRepository->shouldNotReceive('getBySpecialTypeAndRump');
        $moduleRepository->shouldNotReceive('findBy');
        $rump = new SpacecraftRump();
        $moduleLevels = $this->mock(ShipRumpModuleLevelRepositoryInterface::class);
        $moduleLevels->shouldReceive('getByShipRump')->with($rump)->andReturn(new ShipRumpModuleLevel());

        foreach ([$hull, $special] as $module) {
            $selector = new ModuleSelector(
                $moduleRepository,
                $moduleLevels,
                $this->mock(TwigPageInterface::class),
                $module->getType(),
                new Colony(),
                $rump,
                new User(),
                null,
                $plan,
                true
            );

            self::assertSame([$module->getId()], array_keys($selector->getAvailableModules()));
            self::assertSame([$module->getId()], array_keys($selector->getSelectedModules()));
        }
    }

    public function testNpcGiftDoesNotSelectAnotherSpecialModuleWithTheSameName(): void
    {
        $saved = $this->module(19403, SpacecraftModuleTypeEnum::SPECIAL);
        $other = $this->module(19404, SpacecraftModuleTypeEnum::SPECIAL);
        $plan = new SpacecraftBuildplan()->setNpcGift(true);
        $plan->getModules()->set($saved->getId(), new BuildplanModule()
            ->setBuildplan($plan)->setModule($saved)->setModuleType($saved->getType()));
        $repository = $this->mock(ModuleRepositoryInterface::class);
        $repository->shouldNotReceive('findBy');
        $entry = new ModuleSelectorEntry(
            $this->mock(ModuleSelectorInterface::class),
            $other,
            new SpacecraftRump(),
            new ShipRumpModuleLevel(),
            new Colony(),
            new User(),
            $repository,
            $plan,
            true
        );

        self::assertFalse($entry->isChosen());
    }

    public static function editableBuildplans(): iterable
    {
        yield 'regular buildplan' => [false];
        yield 'NPC gift during retrofit' => [true];
    }

    #[DataProvider('editableBuildplans')]
    public function testEditableBuildplanStillUsesRumpRestrictions(bool $npcGift): void
    {
        $repository = $this->mock(ModuleRepositoryInterface::class);
        $repository->shouldReceive('getBySpecialTypeAndRump')
            ->with($colony = new Colony(), SpacecraftModuleTypeEnum::SPECIAL, 8111)
            ->once()->andReturn([]);
        $rump = $this->mock(SpacecraftRump::class);
        $rump->shouldReceive('getId')->andReturn(8111);
        $selector = new ModuleSelector(
            $repository,
            $this->mock(ShipRumpModuleLevelRepositoryInterface::class),
            $this->mock(TwigPageInterface::class),
            SpacecraftModuleTypeEnum::SPECIAL,
            $colony,
            $rump,
            new User(),
            null,
            new SpacecraftBuildplan()->setNpcGift($npcGift)
        );

        self::assertSame([], $selector->getAvailableModules());
    }

    private function module(int $id, SpacecraftModuleTypeEnum $type): Module
    {
        $module = new Module()->setType($type)->setName('Testmodul');
        new ReflectionProperty(Module::class, 'id')->setValue($module, $id);
        return $module;
    }
}
