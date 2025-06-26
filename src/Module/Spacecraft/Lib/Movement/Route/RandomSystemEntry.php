<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Override;
use RuntimeException;
use Stu\Component\Map\DirectionEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

class RandomSystemEntry implements RandomSystemEntryInterface
{
    public function __construct(private StarSystemMapRepositoryInterface $starSystemMapRepository) {}

    #[Override]
    public function getRandomEntryPoint(SpacecraftWrapperInterface $wrapper, StarSystem $system): StarSystemMap
    {
        [$posx, $posy] = $this->getDestinationCoordinates($wrapper, $system);

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
    private function getDestinationCoordinates(SpacecraftWrapperInterface $wrapper, StarSystem $system): array
    {
        $flightDirection = $wrapper->getComputerSystemDataMandatory()->getFlightDirection();
        while ($flightDirection === DirectionEnum::NON) {
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
