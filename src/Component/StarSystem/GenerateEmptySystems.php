<?php

declare(strict_types=1);

namespace Stu\Component\StarSystem;

use Override;
use RuntimeException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\NamesRepositoryInterface;

final class GenerateEmptySystems implements GenerateEmptySystemsInterface
{
    public const int BATCH_AMOUNT = 10;

    public function __construct(private LayerRepositoryInterface $layerRepository, private MapRepositoryInterface $mapRepository, private NamesRepositoryInterface $namesRepository, private StarSystemCreationInterface $starSystemCreation)
    {
    }

    #[Override]
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

        $randomNames = $this->namesRepository->getRandomFreeSystemNames(self::BATCH_AMOUNT);

        foreach ($mapArray as $map) {
            if ($count === self::BATCH_AMOUNT) {
                break;
            }

            $this->starSystemCreation->recreateStarSystem($map, $randomNames[$count]->getName());
            $count++;
        }

        return $count;
    }
}
