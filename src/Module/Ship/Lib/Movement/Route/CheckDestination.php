<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Override;
use RuntimeException;
use Stu\Exception\SanityCheckException;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class CheckDestination implements CheckDestinationInterface
{
    public function __construct(private MapRepositoryInterface $mapRepository, private StarSystemMapRepositoryInterface $starSystemMapRepository)
    {
    }

    #[Override]
    public function validate(
        ShipInterface $ship,
        int $destinationX,
        int $destinationY
    ): MapInterface|StarSystemMapInterface {
        $start = $ship->getCurrentMapField();

        if ($start->getX() !== $destinationX && $start->getY() !== $destinationY) {
            throw new SanityCheckException(
                sprintf(
                    'userId %d tried to navigate from %s to invalid position %d|%d',
                    $ship->getUser()->getId(),
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
        if ($start instanceof StarSystemMapInterface) {

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
