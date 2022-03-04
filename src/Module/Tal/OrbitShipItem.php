<?php

declare(strict_types=1);

namespace Stu\Module\Tal;

use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipRumpInterface;

final class OrbitShipItem implements OrbitShipItemInterface
{
    private ?ShipInterface $ship;

    public function __construct(
        ?ShipInterface $ship
    ) {
        $this->ship = $ship;
    }

    public function getId(): int
    {
        return $this->ship->getId();
    }

    public function getName(): string
    {
        return $this->ship->getName();
    }

    public function getUserName(): string
    {
        return $this->ship->getUser()->getUserName();
    }

    public function isDestroyed(): bool
    {
        return $this->ship->getIsDestroyed();
    }

    public function getCloakState(): bool
    {
        return $this->ship->getCloakState();
    }

    public function getRump(): ShipRumpInterface
    {
        return $this->ship->getRump();
    }

    public function getRumpId(): int
    {
        return $this->ship->getRumpId();
    }

    public function getFormerRumpId(): int
    {
        return $this->ship->getFormerRumpId();
    }

    public function isTrumfield(): bool
    {
        return $this->ship->isTrumfield();
    }

    public function getRumpName(): string
    {
        return $this->ship->getRumpName();
    }

    public function getHull(): int
    {
        return $this->ship->getHuell();
    }

    public function getShield(): int
    {
        return $this->ship->getShield();
    }

    public function getEps(): int
    {
        return $this->ship->getEps();
    }

    public function ownedByUser(): bool
    {
        return $this->ship->isOwnedByCurrentUser();
    }

    public function getHullStatusBar(): string
    {
        return (new TalStatusBar())
            ->setColor(StatusBarColorEnum::STATUSBAR_GREEN)
            ->setLabel(_('Hülle'))
            ->setMaxValue($this->ship->getMaxHuell())
            ->setValue($this->ship->getHuell())
            ->render();
    }

    public function getShieldStatusBar(): string
    {
        return (new TalStatusBar())
            ->setColor(
                $this->ship->getShieldState() === true ? StatusBarColorEnum::STATUSBAR_GREEN : StatusBarColorEnum::STATUSBAR_DARKBLUE
            )
            ->setLabel(_('Schilde'))
            ->setMaxValue($this->ship->getMaxShield())
            ->setValue($this->ship->getShield())
            ->render();
    }

    public function getEpsStatusBar(): string
    {
        return (new TalStatusBar())
            ->setColor(StatusBarColorEnum::STATUSBAR_YELLOW)
            ->setLabel(_('Energie'))
            ->setMaxValue($this->ship->getMaxEps())
            ->setValue($this->ship->getEps())
            ->render();
    }
}
