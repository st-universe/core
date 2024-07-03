<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Override;
use RuntimeException;
use Stu\Component\Ship\ShipEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

class RandomSystemEntry implements RandomSystemEntryInterface
{
    public function __construct(private StarSystemMapRepositoryInterface $starSystemMapRepository)
    {
    }

    #[Override]
    public function getRandomEntryPoint(ShipInterface $ship, StarSystemInterface $system): StarSystemMapInterface
    {
        [$posx, $posy] = $this->getDestinationCoordinates($ship, $system);

        // the destination starsystem map field
        $starsystemMap = $this->starSystemMapRepository->getByCoordinates($system->getId(), $posx, $posy);

        if ($starsystemMap === null) {
            throw new RuntimeException('starsystem map is missing');
        }

        return $starsystemMap;
    }

    /**
     * @return array{0: int,1: int}
     */
    private function getDestinationCoordinates(ShipInterface $ship, StarSystemInterface $system): array
    {
        $flightDirection = $ship->getFlightDirection();
        if ($flightDirection === 0) {
            $flightDirection = random_int(1, 4);
        }

        switch ($flightDirection) {
            case ShipEnum::DIRECTION_BOTTOM:
                $posx = random_int(1, $system->getMaxX());
                $posy = 1;
                break;
            case ShipEnum::DIRECTION_TOP:
                $posx = random_int(1, $system->getMaxX());
                $posy = $system->getMaxY();
                break;
            case ShipEnum::DIRECTION_RIGHT:
                $posx = 1;
                $posy = random_int(1, $system->getMaxY());
                break;
            case ShipEnum::DIRECTION_LEFT:
                $posx = $system->getMaxX();
                $posy = random_int(1, $system->getMaxY());
                break;
            default:
                throw new RuntimeException('unsupported flight direction');
        }

        return [$posx, $posy];
    }
}
