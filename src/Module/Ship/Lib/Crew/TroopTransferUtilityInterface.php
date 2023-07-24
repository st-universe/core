<?php

namespace Stu\Module\Ship\Lib\Crew;

use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;

interface TroopTransferUtilityInterface
{
    public function getFreeQuarters(ShipInterface $ship): int;

    public function getBeamableTroopCount(ShipInterface $ship): int;

    public function ownCrewOnTarget(UserInterface $user, ShipInterface $ship): int;

    public function foreignerCount(ShipInterface $ship): int;
}
