<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Component\Ship\System\Exception\ActivationConditionsNotMetException;
use Stu\Component\Ship\System\Exception\InsufficientCrewException;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Component\Ship\System\Exception\InvalidSystemException;
use Stu\Component\Ship\System\Exception\SystemDamagedException;
use Stu\Component\Ship\System\Exception\SystemNotFoundException;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\StuTestCase;

class ShipSystemManagerTest extends StuTestCase
{

    /**
     * @var null|MockInterface|ShipSystemTypeInterface
     */
    private $system;

    private $system_id = 666;

    /**
     * @var ShipSystemManager|null
     */
    private $manager;

    public function setUp(): void
    {
        $this->system = $this->mock(ShipSystemTypeInterface::class);

        $this->manager = new ShipSystemManager([
            $this->system_id => $this->system
        ]);
    }

    public function testActivateFailsIfSystemNotAvailable(): void
    {
        $this->expectException(SystemNotFoundException::class);

        $ship = $this->mock(ShipInterface::class);

        $ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $this->manager->activate($ship, $this->system_id);
    }

    public function testActivateFailsIfSystemNotActivateble(): void
    {
        $this->expectException(SystemDamagedException::class);

        $ship = $this->mock(ShipInterface::class);
        $shipSystem = $this->mock(ShipSystemInterface::class);

        $ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->system_id => $shipSystem]));

        $shipSystem->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->manager->activate($ship, $this->system_id);
    }

    public function testActivateFailsOnInsufficientEnergy(): void
    {
        $this->expectException(InsufficientEnergyException::class);

        $ship = $this->mock(ShipInterface::class);
        $shipSystem = $this->mock(ShipSystemInterface::class);

        $energyCosts = 666;

        $ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->system_id => $shipSystem]));
        $ship->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $this->system->shouldReceive('getEnergyUsageForActivation')
            ->withNoArgs()
            ->once()
            ->andReturn($energyCosts);

        $shipSystem->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->manager->activate($ship, $this->system_id);
    }

    public function testActivateFailsOnInsufficientCrew(): void
    {
        $this->expectException(InsufficientCrewException::class);

        $ship = $this->mock(ShipInterface::class);
        $shipSystem = $this->mock(ShipSystemInterface::class);

        $energyCosts = 1;

        $ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->system_id => $shipSystem]));
        $ship->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $ship->shouldReceive('getBuildplan->getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $ship->shouldReceive('getCrewCount')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $this->system->shouldReceive('getEnergyUsageForActivation')
            ->withNoArgs()
            ->once()
            ->andReturn($energyCosts);

        $shipSystem->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->manager->activate($ship, $this->system_id);
    }

    public function testActivateFailsIfSystemPreConditionsFail(): void
    {
        $this->expectException(ActivationConditionsNotMetException::class);

        $ship = $this->mock(ShipInterface::class);
        $shipSystem = $this->mock(ShipSystemInterface::class);

        $energyCosts = 1;

        $ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->system_id => $shipSystem]));
        $ship->shouldReceive('getEps')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $ship->shouldReceive('getBuildplan->getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $ship->shouldReceive('getCrewCount')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $this->system->shouldReceive('getEnergyUsageForActivation')
            ->withNoArgs()
            ->once()
            ->andReturn($energyCosts);
        $this->system->shouldReceive('checkActivationConditions')
            ->with($ship)
            ->once()
            ->andReturnFalse();

        $shipSystem->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->manager->activate($ship, $this->system_id);
    }

    public function testActivateActivatesSystem(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $shipSystem = $this->mock(ShipSystemInterface::class);

        $energyCosts = 1;

        $ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$this->system_id => $shipSystem]));
        $ship->shouldReceive('getEps')
            ->withNoArgs()
            ->twice()
            ->andReturn(1);
        $ship->shouldReceive('getBuildplan->getCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $ship->shouldReceive('getCrewCount')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $ship->shouldReceive('setEps')
            ->with(0)
            ->once();

        $this->system->shouldReceive('getEnergyUsageForActivation')
            ->withNoArgs()
            ->twice()
            ->andReturn($energyCosts);
        $this->system->shouldReceive('checkActivationConditions')
            ->with($ship)
            ->once()
            ->andReturnTrue();
        $this->system->shouldReceive('activate')
            ->with($ship)
            ->once();

        $shipSystem->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->manager->activate($ship, $this->system_id);
    }

    public function testDeactivateErrorsOnUnKnownSystem(): void
    {
        $this->expectException(InvalidSystemException::class);

        $ship = $this->mock(ShipInterface::class);

        $this->manager->deactivate($ship, 42);
    }

    public function testDeactivateDeactivates(): void
    {
        $ship = $this->mock(ShipInterface::class);

        $this->system->shouldReceive('deactivate')
            ->with($ship)
            ->once();

        $this->manager->deactivate($ship, $this->system_id);
    }

    public function testDeactivateAllIgnoresDeactivationErrors(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $shipSystem = $this->mock(ShipSystemInterface::class);

        $shipSystem->shouldReceive('getSystemType')
            ->withNoArgs()
            ->once()
            ->andReturn($this->system_id);

        $ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$shipSystem]));
        $ship->shouldReceive('deactivateTractorBeam')
            ->withNoArgs()
            ->once();

        $this->system->shouldReceive('deactivate')
            ->with($ship)
            ->once()
            ->andThrow(new InvalidSystemException());

        $this->manager->deactivateAll($ship);
    }

    public function testDeactivateAllDeactivatesAllSystems(): void
    {
        $ship = $this->mock(ShipInterface::class);
        $shipSystem = $this->mock(ShipSystemInterface::class);

        $shipSystem->shouldReceive('getSystemType')
            ->withNoArgs()
            ->once()
            ->andReturn($this->system_id);

        $ship->shouldReceive('getSystems')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$shipSystem]));
        $ship->shouldReceive('deactivateTractorBeam')
            ->withNoArgs()
            ->once();

        $this->system->shouldReceive('deactivate')
            ->with($ship)
            ->once();

        $this->manager->deactivateAll($ship);
    }
}
