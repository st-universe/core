<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Orm\Entity\SpacecraftEmergencyInterface;
use Stu\Orm\Entity\UserInterface;

final class EmergencyWrapper
{
    public function __construct(private PlayerRelationDeterminatorInterface $playerRelationDeterminator, private SpacecraftEmergencyInterface $emergency, private UserInterface $user)
    {
    }

    public function get(): SpacecraftEmergencyInterface
    {
        return $this->emergency;
    }

    public function showDetails(): bool
    {
        $shipUser = $this->emergency->getShip()->getUser();

        if ($shipUser === $this->user) {
            return true;
        }

        return $this->playerRelationDeterminator->isFriend($this->user, $shipUser);
    }
}
