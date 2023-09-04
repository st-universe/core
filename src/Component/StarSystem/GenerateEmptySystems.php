<?php

declare(strict_types=1);

namespace Stu\Component\StarSystem;

use RuntimeException;
use Stu\Component\StarSystem\StarSystemCreationInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class GenerateEmptySystems implements GenerateEmptySystemsInterface
{
    private LayerRepositoryInterface $layerRepository;

    private MapRepositoryInterface $mapRepository;

    private StarSystemCreationInterface $starSystemCreation;

    public function __construct(
        LayerRepositoryInterface $layerRepository,
        MapRepositoryInterface $mapRepository,
        StarSystemCreationInterface $starSystemCreation
    ) {
        $this->layerRepository = $layerRepository;
        $this->mapRepository = $mapRepository;
        $this->starSystemCreation = $starSystemCreation;
    }

    public function generate(int $layerId): int
    {
        $layer = $this->layerRepository->find($layerId);
        if ($layer === null) {
            throw new RuntimeException('layer does not exist');
        }

        $mapArray = $this->mapRepository->getWithEmptySystem($layer);

        $count = 0;

        foreach ($mapArray as $map) {
            $this->starSystemCreation->recreateStarSystem($map);
            $count++;

            if ($count === 10) {
                break;
            }
        }

        return $count;
    }
}
