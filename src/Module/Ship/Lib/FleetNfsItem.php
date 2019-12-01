<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Doctrine\Common\Collections\Collection;
use Stu\Lib\SessionInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;

final class FleetNfsItem implements FleetNfsItemInterface
{
    private SessionInterface $session;

    private FleetInterface $fleet;

    private ShipInterface $currentShip;

    public function __construct(
        SessionInterface $session,
        FleetInterface $fleet,
        ShipInterface $currentShip
    ) {
        $this->session = $session;
        $this->fleet = $fleet;
        $this->currentShip = $currentShip;
    }

    public function isVisisble(): bool
    {
        $fleetShips = $this->fleet->getShips();

        if (
            $this->fleet->getUser() === $this->currentShip->getUser() && (
                ($fleetShips->containsKey($this->currentShip->getId()) && $fleetShips->count() > 1) ||
                $fleetShips->count() > 0
            )
        ) {
            return true;
        }
        foreach ($this->fleet->getShips() as $ship) {
            if ($ship->getCloakState() === false) {
                return true;
            }
        }
        return false;
    }

    public function isHidden(): bool {
        return $this->session->hasSessionValue('hiddenfleets', $this->fleet->getId());
    }

    public function getVisibleShips(): Collection
    {
        return $this->fleet->getShips()
            ->filter(
                function (ShipInterface $ship): bool {
                    return $ship !== $this->currentShip && (
                        $ship->getCloakState() === false ||
                        $ship->getUser() === $this->currentShip->getUser()
                    );
                }
            );
    }

    public function showManagement(): bool
    {
        return $this->fleet->getUser() === $this->currentShip->getUser();
    }

    public function getName(): string
    {
        return $this->fleet->getName();
    }

    public function getId(): int
    {
        return $this->fleet->getId();
    }

    public function getLeadShip(): ShipInterface
    {
        return $this->fleet->getLeadShip();
    }

    public function getUser(): UserInterface
    {
        return $this->fleet->getUser();
    }
}
