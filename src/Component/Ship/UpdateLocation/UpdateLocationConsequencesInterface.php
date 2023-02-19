<?php

namespace Stu\Component\Ship\UpdateLocation;

use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;

interface UpdateLocationConsequencesInterface
{
    /**
     * @param StarSystemMapInterface|MapInterface $nextField
     * @param null|list<string> $msgToPlayer
     */
    public function updateLocationWithConsequences(
        ShipWrapperInterface $wrapper,
        ?ShipInterface $tractoringShip,
        $nextField,
        array &$msgToPlayer = null
    ): void;
}
