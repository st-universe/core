<?php

declare(strict_types=1);

namespace Stu\Lib\ModuleRumpWrapper;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftCondition;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\SpacecraftRumpBaseValues;
use Stu\StuTestCase;

class ModuleRumpWrapperHullTest extends StuTestCase
{
    private MockInterface&SpacecraftRump $rump;
    private MockInterface&SpacecraftRumpBaseValues $baseValues;
    private MockInterface&SpacecraftBuildplan $buildplan;
    private MockInterface&Module $module;
    private MockInterface&SpacecraftWrapperInterface $wrapper;
    private MockInterface&Ship $ship;
    private MockInterface&SpacecraftCondition $condition;

    private ModuleRumpWrapperHull $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->rump = $this->mock(SpacecraftRump::class);
        $this->baseValues = $this->mock(SpacecraftRumpBaseValues::class);
        $this->buildplan = $this->mock(SpacecraftBuildplan::class);
        $this->module = $this->mock(Module::class);
        $this->wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $this->ship = $this->mock(Ship::class);
        $this->condition = $this->mock(SpacecraftCondition::class);

        $this->rump->shouldReceive('getBaseValues')
            ->withNoArgs()
            ->andReturn($this->baseValues);
        $this->buildplan->shouldReceive('getModulesByType')
            ->with(SpacecraftModuleTypeEnum::HULL)
            ->andReturn(new ArrayCollection([$this->module]));
        $this->baseValues->shouldReceive('getBaseHull')
            ->withNoArgs()
            ->andReturn(100);
        $this->baseValues->shouldReceive('getModuleLevel')
            ->withNoArgs()
            ->andReturn(1);
        $this->module->shouldReceive('getType')
            ->withNoArgs()
            ->andReturn(SpacecraftModuleTypeEnum::HULL);
        $this->module->shouldReceive('getLevel')
            ->withNoArgs()
            ->andReturn(1);
        $this->module->shouldReceive('getDefaultFactor')
            ->withNoArgs()
            ->andReturn(20);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('getCondition')
            ->withNoArgs()
            ->andReturn($this->condition);

        $this->subject = new ModuleRumpWrapperHull($this->rump, $this->buildplan);
    }

    public function testApplyKeepsExistingHullPercentageWhenMaxHullChanges(): void
    {
        $this->condition->shouldReceive('getHull')
            ->withNoArgs()
            ->once()
            ->andReturn(95);
        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()
            ->once()
            ->andReturn(100);
        $this->condition->shouldReceive('setHull')
            ->with(114)
            ->once()
            ->andReturnSelf();
        $this->ship->shouldReceive('setMaxHull')
            ->with(120)
            ->once()
            ->andReturnSelf();

        $this->subject->apply($this->wrapper);
    }

    public function testApplyInitializesFullHullWhenNoPreviousMaxHullExists(): void
    {
        $this->condition->shouldReceive('getHull')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $this->ship->shouldReceive('getMaxHull')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $this->condition->shouldReceive('setHull')
            ->with(120)
            ->once()
            ->andReturnSelf();
        $this->ship->shouldReceive('setMaxHull')
            ->with(120)
            ->once()
            ->andReturnSelf();

        $this->subject->apply($this->wrapper);
    }
}
