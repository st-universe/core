<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Override;
use RuntimeException;
use Stu\Component\Map\DirectionEnum;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

class RandomSystemEntry implements RandomSystemEntryInterface
{
    public function __construct(private StarSystemMapRepositoryInterface $starSystemMapRepository) {}

    #[Override]
    public function getRandomEntryPoint(SpacecraftInterface $spacecraft, StarSystemInterface $system): StarSystemMapInterface
    {
        [$posx, $posy] = $this->getDestinationCoordinates($spacecraft, $system);

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
    private function getDestinationCoordinates(SpacecraftInterface $spacecraft, StarSystemInterface $system): array
    {
        $flightDirection = $spacecraft->getFlightDirection();
        if ($flightDirection === null) {
            $flightDirection = DirectionEnum::from(random_int(1, 4));
        }

        switch ($flightDirection) {
            case DirectionEnum::BOTTOM:
                $posx = random_int(1, $system->getMaxX());
                $posy = 1;
                break;
            case DirectionEnum::TOP:
                $posx = random_int(1, $system->getMaxX());
                $posy = $system->getMaxY();
                break;
            case DirectionEnum::RIGHT:
                $posx = 1;
                $posy = random_int(1, $system->getMaxY());
                break;
            case DirectionEnum::LEFT:
                $posx = $system->getMaxX();
                $posy = random_int(1, $system->getMaxY());
                break;
        }

        return [$posx, $posy];
    }
}
