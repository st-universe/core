<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Orm\Entity\SpacecraftEmergencyInterface;
use Stu\Orm\Entity\UserInterface;

final class EmergencyWrapper
{
    private SpacecraftEmergencyInterface $emergency;

    private UserInterface $user;

    public function __construct(
        SpacecraftEmergencyInterface $emergency,
        UserInterface $user
    ) {
        $this->emergency = $emergency;
        $this->user = $user;
    }

    public function get(): SpacecraftEmergencyInterface
    {
        return $this->emergency;
    }

    public function showDetails(): bool
    {
        return $this->user->isFriend($this->emergency->getShip()->getUser()->getId());
    }
}
