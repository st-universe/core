<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Fleet;

use Stu\Orm\Entity\Ship;

final class LeaveFleet implements LeaveFleetInterface
{
    public function __construct(
        private ChangeFleetLeaderInterface $changeFleetLeader
    ) {}

    #[\Override]
    public function leaveFleet(Ship $ship): bool
    {
        $fleet = $ship->getFleet();

        if ($fleet === null) {
            return false;
        }

        if ($ship->isFleetLeader()) {
            $this->changeFleetLeader->change($ship);
        } else {
            // Initialize the fleet's ships collection to ensure Doctrine properly tracks the removal
            // This is necessary due to Doctrine's lazy-loading behavior with inverse-side OneToMany relationships
            $fleet->getShips()->getValues();

            $ship->setFleet(null);
            $ship->setIsFleetLeader(false);
        }

        return true;
    }
}
