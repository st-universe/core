<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Orm\Entity\SpacecraftEmergencyInterface;
use Stu\Orm\Entity\UserInterface;

final class EmergencyWrapper
{
    private SpacecraftEmergencyInterface $emergency;

    private UserInterface $user;

    private PlayerRelationDeterminatorInterface $playerRelationDeterminator;

    public function __construct(
        PlayerRelationDeterminatorInterface $playerRelationDeterminator,
        SpacecraftEmergencyInterface $emergency,
        UserInterface $user
    ) {
        $this->playerRelationDeterminator = $playerRelationDeterminator;
        $this->emergency = $emergency;
        $this->user = $user;
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
