<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use RuntimeException;
use Stu\Exception\SanityCheckException;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class CheckDestination implements CheckDestinationInterface
{
    public function __construct(private MapRepositoryInterface $mapRepository, private StarSystemMapRepositoryInterface $starSystemMapRepository) {}

    #[\Override]
    public function validate(
        Spacecraft $spacecraft,
        int $destinationX,
        int $destinationY
    ): Map|StarSystemMap {
        $start = $spacecraft->getLocation();

        if ($start->getX() !== $destinationX && $start->getY() !== $destinationY) {
            throw new SanityCheckException(
                sprintf(
                    'userId %d tried to navigate from %s to invalid position %d|%d',
                    $spacecraft->getUser()->getId(),
                    $start->getSectorString(),
                    $destinationX,
                    $destinationY
                )
            );
        }
        if ($destinationX < 1) {
            $destinationX = 1;
        }
        if ($destinationY < 1) {
            $destinationY = 1;
        }
        if ($start instanceof StarSystemMap) {

            $system = $start->getSystem();
            if ($destinationX > $system->getMaxX()) {
                $destinationX = $system->getMaxX();
            }
            if ($destinationY > $system->getMaxY()) {
                $destinationY = $system->getMaxY();
            }

            $result = $this->starSystemMapRepository->getByCoordinates($system->getId(), $destinationX, $destinationY);
        } else {
            $layer = $start->getLayer();
            if ($layer === null) {
                throw new RuntimeException('this should not happen');
            }

            if ($destinationX > $layer->getWidth()) {
                $destinationX = $layer->getWidth();
            }
            if ($destinationY > $layer->getHeight()) {
                $destinationY = $layer->getHeight();
            }

            $result = $this->mapRepository->getByCoordinates($layer, $destinationX, $destinationY);
        }

        if ($result === null) {
            throw new RuntimeException(sprintf('destination %d|%d does not exist', $destinationX, $destinationY));
        }

        return $result;
    }
}
