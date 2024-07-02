<?php

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\Battle\Party\AttackedBattleParty;
use Stu\Module\Ship\Lib\Battle\Party\AttackingBattleParty;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipNfsItem;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

interface FightLibInterface
{
    public function ready(ShipWrapperInterface $wrapper, InformationInterface $informations): void;

    public function canAttackTarget(
        ShipInterface $ship,
        ShipInterface|ShipNfsItem $nfsItem,
        bool $checkCloaked = false,
        bool $checkActiveWeapons = true,
        bool $checkWarped = true
    ): bool;

    /**
     * @return array{0: AttackingBattleParty, 1: AttackedBattleParty, 2: bool}
     */
    public function getAttackersAndDefenders(
        ShipWrapperInterface|FleetWrapperInterface $wrapper,
        ShipWrapperInterface $target,
        BattlePartyFactoryInterface $battlePartyFactory
    ): array;

    public function isTargetOutsideFinishedTholianWeb(ShipInterface $ship, ShipInterface $target): bool;

    public function calculateHealthPercentage(ShipInterface $target): int;
}
