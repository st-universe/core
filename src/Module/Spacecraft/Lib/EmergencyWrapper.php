<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Orm\Entity\SpacecraftEmergency;
use Stu\Orm\Entity\User;

final class EmergencyWrapper
{
    public function __construct(private PlayerRelationDeterminatorInterface $playerRelationDeterminator, private SpacecraftEmergency $emergency, private User $user) {}

    public function get(): SpacecraftEmergency
    {
        return $this->emergency;
    }

    public function showDetails(): bool
    {
        $shipUser = $this->emergency->getSpacecraft()->getUser();

        if ($shipUser === $this->user) {
            return true;
        }

        return $this->playerRelationDeterminator->isFriend($this->user, $shipUser);
    }
}
