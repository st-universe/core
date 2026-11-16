<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Crew\CrewTypeEnum;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Orm\Entity\Ship;

final class BorgSphereCrew
{
    public function __construct(private CrewCreatorInterface $creator) {}

    public function refill(Ship $ship): void
    {
        $missing = max(0, $ship->getNeededCrewCount() - $ship->getCrewCount());
        for ($index = 0; $index < $missing; $index++) {
            $assignment = $this->creator->create($ship->getUser()->getId())
                ->setSpacecraft($ship)
                ->setSlot(CrewTypeEnum::CREWMAN);
            $ship->getCrewAssignments()->add($assignment);
        }
    }
}
