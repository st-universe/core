<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Exception\AccessViolation;
use Stu\Exception\ShipDoesNotExistException;
use Stu\Exception\ShipIsDestroyedException;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipLoader implements ShipLoaderInterface
{
    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipRepository = $shipRepository;
    }

    public function getByIdAndUser(int $shipId, int $userId): ShipInterface
    {
        $ship = $this->shipRepository->find($shipId);

        if ($ship === null) {
            throw new ShipDoesNotExistException();
        }

        if ($ship->getIsDestroyed()) {
            throw new ShipIsDestroyedException();
        }

        if ($ship->getUser()->getId() != $userId) {
            throw new AccessViolation();
        }

        return $ship;
    }
}
