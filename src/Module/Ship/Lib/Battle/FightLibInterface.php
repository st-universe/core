<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Ship\Lib\ShipNfsItem;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

interface FightLibInterface
{
    public function ready(ShipWrapperInterface $wrapper): InformationWrapper;

    /**
     * @param ShipWrapperInterface[] $base
     *
     * @return array<int, ShipWrapperInterface>
     */
    public function filterInactiveShips(array $base): array;

    public function canFire(ShipWrapperInterface $wrapper): bool;

    public function canAttackTarget(
        ShipInterface $ship,
        ShipInterface|ShipNfsItem $nfsItem,
        bool $checkActiveWeapons = true,
        bool $checkActiveWarpdrive = true
    ): bool;

    /** @return array<int, ShipWrapperInterface> */
    public function getAttackers(ShipWrapperInterface $wrapper): array;

    /**
     * @return array{0: array<int, ShipWrapperInterface>, 1: array<int, ShipWrapperInterface>, 2: bool}
     */
    public function getAttackersAndDefenders(ShipWrapperInterface $wrapper, ShipWrapperInterface $target): array;

    public function isTargetOutsideFinishedTholianWeb(ShipInterface $ship, ShipInterface $target): bool;

    public function calculateHealthPercentage(ShipInterface $target): int;
}
