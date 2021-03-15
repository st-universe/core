<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Doctrine\Common\Collections\Collection;
use Stu\Lib\SessionInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;

final class FleetNfsItem implements FleetNfsItemInterface
{
    private SessionInterface $session;

    private FleetInterface $fleet;

    private ShipInterface $currentShip;

    private bool $showCloaked;

    public function __construct(
        SessionInterface $session,
        FleetInterface $fleet,
        ShipInterface $currentShip,
        bool $showCloaked
    ) {
        $this->session = $session;
        $this->fleet = $fleet;
        $this->currentShip = $currentShip;
        $this->showCloaked = $showCloaked;
    }

    public function isHidden(): bool
    {
        return $this->session->hasSessionValue('hiddenfleets', $this->fleet->getId());
    }

    public function getVisibleShips(): Collection
    {
        return $this->fleet->getShips()
            ->filter(
                function (ShipInterface $ship): bool {
                    return $ship !== $this->currentShip && ($this->showCloaked ||
                        $ship->getCloakState() === false ||
                        $ship->getUser() === $this->currentShip->getUser());
                }
            );
    }

    public function isFleetOfCurrentShip(): bool
    {
        return $this->fleet->getShips()->containsKey($this->currentShip->getId());
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

    public function getDefendedColony(): ?ColonyInterface
    {
        return $this->fleet->getDefendedColony();
    }

    public function getBlockedColony(): ?ColonyInterface
    {
        return $this->fleet->getBlockedColony();
    }
}
