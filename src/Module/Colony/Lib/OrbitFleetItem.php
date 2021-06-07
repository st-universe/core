<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\FleetInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;

final class OrbitFleetItem implements OrbitFleetItemInterface
{
    private int $fleetId;

    private array $shipList;

    private int $userId;

    private FleetInterface $fleet;

    public function __construct(
        int $fleetId,
        array $shipList,
        int $userId
    ) {
        $this->userId = $userId;
        $this->shipList = $shipList;
        $this->fleetId = $fleetId;
    }

    private function loadFleet()
    {
        // @todo refactor
        global $container;
        $fleetRepo = $container->get(FleetRepositoryInterface::class);

        $this->fleet =  $fleetRepo->find($this->fleetId);
    }

    public function isFleetOwnedByUser(): bool
    {
        if ($this->fleetId == 0) {
            return false;
        }

        if ($this->fleet == null) {
            $this->loadFleet();
        }

        return $this->fleet->getUserId() == $this->userId;
    }

    public function getName(): string
    {
        if ($this->fleetId == 0) {
            return _('Einzelschiffe');
        }

        if ($this->fleet == null) {
            $this->loadFleet();
        }

        return $this->fleet->getName();
    }

    public function getOwnerName(): ?string
    {
        if ($this->fleetId == 0) {
            return null;
        }

        if ($this->fleet == null) {
            $this->loadFleet();
        }

        return $this->fleet->getUser()->getName();
    }

    public function getSort(): int
    {
        if ($this->fleetId == 0) {
            return 0;
        }

        if ($this->fleet == null) {
            $this->loadFleet();
        }

        return $this->fleet->getSort();
    }

    public function getShips(): array
    {
        return $this->shipList;
    }
}
