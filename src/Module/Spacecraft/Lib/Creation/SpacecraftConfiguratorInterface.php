<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\LocationInterface;

/**
 * @template T of SpacecraftWrapperInterface
 */
interface SpacecraftConfiguratorInterface
{
    /** @return SpacecraftConfiguratorInterface<T> */
    public function setLocation(LocationInterface $location): SpacecraftConfiguratorInterface;

    /** @return SpacecraftConfiguratorInterface<T> */
    public function loadEps(int $percentage): SpacecraftConfiguratorInterface;

    /** @return SpacecraftConfiguratorInterface<T> */
    public function loadBattery(int $percentage): SpacecraftConfiguratorInterface;

    /** @return SpacecraftConfiguratorInterface<T> */
    public function loadReactor(int $percentage): SpacecraftConfiguratorInterface;

    /** @return SpacecraftConfiguratorInterface<T> */
    public function loadWarpdrive(int $percentage): SpacecraftConfiguratorInterface;

    /** @return SpacecraftConfiguratorInterface<T> */
    public function createCrew(?int $amount = null): SpacecraftConfiguratorInterface;

    /** @return SpacecraftConfiguratorInterface<T> */
    public function setAlertState(SpacecraftAlertStateEnum $alertState): SpacecraftConfiguratorInterface;

    /** @return SpacecraftConfiguratorInterface<T> */
    public function setTorpedo(?int $torpedoTypeId = null): SpacecraftConfiguratorInterface;

    /** @return SpacecraftConfiguratorInterface<T> */
    public function maxOutSystems(): SpacecraftConfiguratorInterface;

    /** @return SpacecraftConfiguratorInterface<T> */
    public function setSpacecraftName(string $name): SpacecraftConfiguratorInterface;

    /** @return T */
    public function finishConfiguration();
}
