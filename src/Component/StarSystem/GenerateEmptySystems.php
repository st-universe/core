<?php

declare(strict_types=1);

namespace Stu\Component\StarSystem;

use RuntimeException;
use Stu\Component\StarSystem\StarSystemCreationInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemRepositoryInterface;

final class GenerateEmptySystems implements GenerateEmptySystemsInterface
{
    public const BATCH_AMOUNT = 10;

    private LayerRepositoryInterface $layerRepository;

    private MapRepositoryInterface $mapRepository;

    private StarSystemRepositoryInterface $starSystemRepository;

    private StarSystemCreationInterface $starSystemCreation;

    public function __construct(
        LayerRepositoryInterface $layerRepository,
        MapRepositoryInterface $mapRepository,
        StarSystemRepositoryInterface $starSystemRepository,
        StarSystemCreationInterface $starSystemCreation
    ) {
        $this->layerRepository = $layerRepository;
        $this->mapRepository = $mapRepository;
        $this->starSystemRepository = $starSystemRepository;
        $this->starSystemCreation = $starSystemCreation;
    }

    public function generate(int $layerId, ?GameControllerInterface $game): int
    {
        $layer = $this->layerRepository->find($layerId);
        if ($layer === null) {
            throw new RuntimeException('layer does not exist');
        }

        if ($layer->isFinished()) {
            if ($game !== null) {
                $game->addInformation('Der Layer ist fertig, kein Neugenerierung mehr möglich');
            }
            return 0;
        }

        $mapArray = $this->mapRepository->getWithEmptySystem($layer);

        $count = 0;

        $randomNames = $this->starSystemRepository->getRandomFreeSystemNames(self::BATCH_AMOUNT);

        foreach ($mapArray as $map) {
            if ($count === self::BATCH_AMOUNT) {
                break;
            }

            $this->starSystemCreation->recreateStarSystem($map,  $randomNames[$count]->getName());
            $count++;
        }

        return $count;
    }
}
