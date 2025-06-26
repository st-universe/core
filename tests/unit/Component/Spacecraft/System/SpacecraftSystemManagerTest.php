<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\Exception\ActivationConditionsNotMetException;
use Stu\Component\Spacecraft\System\Exception\AlreadyActiveException;
use Stu\Component\Spacecraft\System\Exception\AlreadyOffException;
use Stu\Component\Spacecraft\System\Exception\DeactivationConditionsNotMetException;
use Stu\Component\Spacecraft\System\Exception\InsufficientCrewException;
use Stu\Component\Spacecraft\System\Exception\InsufficientEnergyException;
use Stu\Component\Spacecraft\System\Exception\InvalidSystemException;
use Stu\Component\Spacecraft\System\Exception\SystemCooldownException;
use Stu\Component\Spacecraft\System\Exception\SystemDamagedException;
use Stu\Component\Spacecraft\System\Exception\SystemNotActivatableException;
use Stu\Component\Spacecraft\System\Exception\SystemNotDeactivatableException;
use Stu\Component\Spacecraft\System\Exception\SystemNotFoundException;
use Stu\Module\Control\StuTime;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\StuTestCase;

class SpacecraftSystemManagerTest extends StuTestCase
{
    private MockInterface&Ship $ship;
    private MockInterface&ShipWrapperInterface $wrapper;
    private MockInterface&SpacecraftSystem $shipSystem;
    private MockInterface&SpacecraftSystemTypeInterface $systemType;

    private MockInterface&StuTime $stuTimeMock;

    private SpacecraftSystemTypeEnum $system_id = SpacecraftSystemTypeEnum::EPS;

    private SpacecraftSystemManagerInterface $manager;

    #[Override]
    public function setUp(): void
    {
        $this->ship = $this->mock(Ship::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->shipSystem = $this->mock(SpacecraftSystem::class);
        $this->systemType = $this->mock(SpacecraftSystemTypeInterface::class);

        $this->stuTimeMock = $this->mock(StuTime::class);

        $this->manager = new SpacecraftSystemManager([
            $this->system_id->value => $this->systemType,
        ], $this->stuTimeMock);
    }

    public function testActivateFailsIfSystemNotAvailable(): void
    {
        $this->expectException(SystemNotFoundException::class);

        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $this->stuTimeMock->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        //wrapper
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);


        $this->manager->activate($this->wrapper, $this->system_id);
    }

    public function testActivateFailsIfSystemDestroyed(): void
    {
        $this->expectException(SystemDamagedException::class);

        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->system_id->value =>  $this->shipSystem]));

        //wrapper
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->shipSystem->shouldReceive('getStatus')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $this->stuTimeMock->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->manager->activate($this->wrapper, $this->system_id);
    }

    public function testActivateFailsIfSystemNotActivatable(): void
    {
        $this->expectException(SystemNotActivatableException::class);

        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->system_id->value =>  $this->shipSystem]));

        //wrapper
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->shipSystem->shouldReceive('getStatus')
            ->withNoArgs()
            ->once()
            ->andReturn(100);

        $this->shipSystem->shouldReceive('getMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_ALWAYS_OFF);

        $this->stuTimeMock->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->manager->activate($this->wrapper, $this->system_id);
    }

    public function testActivateFailsIfSystemAlreadyOn(): void
    {
        $this->expectException(AlreadyActiveException::class);

        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->system_id->value =>  $this->shipSystem]));

        //wrapper
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->shipSystem->shouldReceive('getStatus')
            ->withNoArgs()
            ->once()
            ->andReturn(100);

        $this->shipSystem->shouldReceive('getMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_ON);

        $this->stuTimeMock->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->manager->activate($this->wrapper, $this->system_id);
    }

    public function testActivateFailsOnInsufficientCrew(): void
    {
        $this->expectException(InsufficientCrewException::class);

        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->system_id->value =>  $this->shipSystem]));
        $this->ship->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        //wrapper
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->shipSystem->shouldReceive('getStatus')
            ->withNoArgs()
            ->once()
            ->andReturn(100);

        $this->shipSystem->shouldReceive('getMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);

        $this->stuTimeMock->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->manager->activate($this->wrapper, $this->system_id);
    }

    public function testActivateFailsOnInsufficientEnergy(): void
    {
        $this->expectException(InsufficientEnergyException::class);
        $epsSystem = $this->mock(EpsSystemData::class);

        $energyCosts = 2;

        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->system_id->value =>  $this->shipSystem]));

        //wrapper and eps
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystem);
        $epsSystem->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $this->ship->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->systemType->shouldReceive('getEnergyUsageForActivation')
            ->withNoArgs()
            ->twice()
            ->andReturn($energyCosts);

        $this->shipSystem->shouldReceive('getStatus')
            ->withNoArgs()
            ->once()
            ->andReturn(100);

        $this->shipSystem->shouldReceive('getMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);

        $this->stuTimeMock->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->manager->activate($this->wrapper, $this->system_id);
    }

    public function testActivateFailsIfSystemPreConditionsFail(): void
    {
        $this->expectException(ActivationConditionsNotMetException::class);
        $epsSystem = $this->mock(EpsSystemData::class);

        $energyCosts = 1;

        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->system_id->value =>  $this->shipSystem]));

        //wrapper and eps
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystem);
        $epsSystem->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $this->ship->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->systemType->shouldReceive('getEnergyUsageForActivation')
            ->withNoArgs()
            ->once()
            ->andReturn($energyCosts);
        $this->systemType->shouldReceive('checkActivationConditions')->with(
            $this->wrapper,
            Mockery::on(function (&$reason): bool {
                $reason = 'reason';
                return true;
            })
        )->once()
            ->andReturnFalse();

        $this->shipSystem->shouldReceive('getStatus')
            ->withNoArgs()
            ->once()
            ->andReturn(100);

        $this->shipSystem->shouldReceive('getMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);
        $this->shipSystem->shouldReceive('getCooldown')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->stuTimeMock->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->manager->activate($this->wrapper, $this->system_id);
    }

    public function testActivateDryRun(): void
    {
        $energyCosts = 1;
        $epsSystem = $this->mock(EpsSystemData::class);

        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->system_id->value =>  $this->shipSystem]));

        //wrapper and eps
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystem);
        $epsSystem->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $this->ship->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->systemType->shouldReceive('getEnergyUsageForActivation')
            ->withNoArgs()
            ->once()
            ->andReturn($energyCosts);
        $this->systemType->shouldReceive('checkActivationConditions')
            ->with($this->wrapper, Mockery::any())
            ->once()
            ->andReturnTrue();

        $this->shipSystem->shouldReceive('getStatus')
            ->withNoArgs()
            ->once()
            ->andReturn(100);

        $this->shipSystem->shouldReceive('getMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);
        $this->shipSystem->shouldReceive('getCooldown')
            ->withNoArgs()
            ->once()
            ->andReturnNull();
        $this->stuTimeMock->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->manager->activate($this->wrapper, $this->system_id, false, true);
    }

    public function testActivateActivatesSystemNoCooldown(): void
    {
        $energyCosts = 1;
        $epsSystem = $this->mock(EpsSystemData::class);

        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->twice()
            ->andReturn(new ArrayCollection([$this->system_id->value =>  $this->shipSystem]));

        //wrapper and eps
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->twice()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->twice()
            ->andReturn($epsSystem);
        $epsSystem->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $epsSystem->shouldReceive('lowerEps')
            ->with(1)
            ->once()
            ->andReturnSelf();
        $epsSystem->shouldReceive('update')
            ->withNoArgs()
            ->once();

        $this->ship->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->systemType->shouldReceive('getEnergyUsageForActivation')
            ->withNoArgs()
            ->twice()
            ->andReturn($energyCosts);
        $this->systemType->shouldReceive('getCooldownSeconds')
            ->withNoArgs()
            ->once()
            ->andReturnNull();
        $this->systemType->shouldReceive('checkActivationConditions')
            ->with($this->wrapper, Mockery::any())
            ->once()
            ->andReturnTrue();
        $this->systemType->shouldReceive('activate')
            ->with($this->wrapper, $this->manager)
            ->once();

        $this->shipSystem->shouldReceive('getStatus')
            ->withNoArgs()
            ->once()
            ->andReturn(100);

        $this->shipSystem->shouldReceive('getMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);
        $this->shipSystem->shouldReceive('getCooldown')
            ->withNoArgs()
            ->once()
            ->andReturnNull();
        $this->stuTimeMock->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->manager->activate($this->wrapper, $this->system_id);
    }

    public function testActivateActivatesSystemOldCooldown(): void
    {
        $energyCosts = 1;
        $epsSystem = $this->mock(EpsSystemData::class);

        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->twice()
            ->andReturn(new ArrayCollection([$this->system_id->value =>  $this->shipSystem]));

        //wrapper and eps
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->twice()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->twice()
            ->andReturn($epsSystem);
        $epsSystem->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $epsSystem->shouldReceive('lowerEps')
            ->with(1)
            ->once()
            ->andReturnSelf();
        $epsSystem->shouldReceive('update')
            ->withNoArgs()
            ->once();


        $this->ship->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->systemType->shouldReceive('getEnergyUsageForActivation')
            ->withNoArgs()
            ->twice()
            ->andReturn($energyCosts);
        $this->systemType->shouldReceive('getCooldownSeconds')
            ->withNoArgs()
            ->twice()
            ->andReturn(5);
        $this->systemType->shouldReceive('checkActivationConditions')
            ->with($this->wrapper, Mockery::any())
            ->once()
            ->andReturnTrue();
        $this->systemType->shouldReceive('activate')
            ->with($this->wrapper, $this->manager)
            ->once();

        $this->shipSystem->shouldReceive('getStatus')
            ->withNoArgs()
            ->once()
            ->andReturn(100);

        $this->shipSystem->shouldReceive('getMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);
        $this->shipSystem->shouldReceive('getCooldown')
            ->withNoArgs()
            ->once()
            ->andReturn(41);
        $this->shipSystem->shouldReceive('setCooldown')
            ->with(47)
            ->once();

        $this->stuTimeMock->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->manager->activate($this->wrapper, $this->system_id);
    }

    public function testActivateExpectCooldownExceptionWhenLastingCooldown(): void
    {
        $this->expectException(SystemCooldownException::class);
        $epsSystem = $this->mock(EpsSystemData::class);

        $energyCosts = 1;

        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->system_id->value =>  $this->shipSystem]));

        //wrapper and eps
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->wrapper->shouldReceive('getEpsSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($epsSystem);
        $epsSystem->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $this->ship->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->systemType->shouldReceive('getEnergyUsageForActivation')
            ->withNoArgs()
            ->once()
            ->andReturn($energyCosts);

        $this->shipSystem->shouldReceive('getStatus')
            ->withNoArgs()
            ->once()
            ->andReturn(100);

        $this->shipSystem->shouldReceive('getMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);
        $this->shipSystem->shouldReceive('getCooldown')
            ->withNoArgs()
            ->once()
            ->andReturn(43);

        $this->stuTimeMock->shouldReceive('time')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->manager->activate($this->wrapper, $this->system_id);
    }

    public function testDeactivateErrorsOnUnKnownSystem(): void
    {
        $this->expectException(InvalidSystemException::class);

        $this->manager->deactivate($this->wrapper, SpacecraftSystemTypeEnum::FUSION_REACTOR);
    }

    public function testDeactivateErrorsOnNotDeactivatable(): void
    {
        $this->expectException(SystemNotDeactivatableException::class);

        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->system_id->value =>  $this->shipSystem]));
        $this->shipSystem->shouldReceive('getMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_ALWAYS_ON);
        //wrapper
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->manager->deactivate($this->wrapper, $this->system_id);
    }

    public function testDeactivateErrorsOnAlreadyOff(): void
    {
        $this->expectException(AlreadyOffException::class);

        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->system_id->value =>  $this->shipSystem]));
        $this->shipSystem->shouldReceive('getMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_OFF);
        //wrapper
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->manager->deactivate($this->wrapper, $this->system_id);
    }

    public function testDeactivateErrorsIfSystemPreConditionsFail(): void
    {
        $this->expectException(DeactivationConditionsNotMetException::class);

        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->system_id->value =>  $this->shipSystem]));
        $this->shipSystem->shouldReceive('getMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_ON);
        //wrapper
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->systemType->shouldReceive('checkDeactivationConditions')->with(
            $this->wrapper,
            Mockery::on(function (&$reason): bool {
                $reason = 'reason';
                return true;
            })
        )->once()
            ->andReturnFalse();

        $this->manager->deactivate($this->wrapper, $this->system_id);
    }

    public function testDeactivateDeactivates(): void
    {
        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->system_id->value =>  $this->shipSystem]));
        $this->systemType->shouldReceive('checkDeactivationConditions')
            ->with($this->wrapper, Mockery::any())
            ->once()
            ->andReturnTrue();

        $this->shipSystem->shouldReceive('getMode')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftSystemModeEnum::MODE_ON);

        $this->systemType->shouldReceive('deactivate')
            ->with($this->wrapper)
            ->once();
        //wrapper
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->manager->deactivate($this->wrapper, $this->system_id);
    }

    public function testDeactivateAllIgnoresDeactivationErrors(): void
    {
        $this->shipSystem->shouldReceive('getSystemType')
            ->withNoArgs()
            ->once()
            ->andReturn($this->system_id);

        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->shipSystem]));

        $this->systemType->shouldReceive('deactivate')
            ->with($this->wrapper)
            ->once()
            ->andThrow(new InvalidSystemException());
        //wrapper
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->manager->deactivateAll($this->wrapper);
    }

    public function testDeactivateAllDeactivatesAllSystems(): void
    {
        $this->shipSystem->shouldReceive('getSystemType')
            ->withNoArgs()
            ->once()
            ->andReturn($this->system_id);

        $this->ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->shipSystem]));
        $this->systemType->shouldReceive('deactivate')
            ->with($this->wrapper)
            ->once();
        //wrapper
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);

        $this->manager->deactivateAll($this->wrapper);
    }
}
