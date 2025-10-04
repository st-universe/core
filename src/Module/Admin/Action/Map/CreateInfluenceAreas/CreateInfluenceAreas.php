<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\CreateInfluenceAreas;

use Override;
use request;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class CreateInfluenceAreas implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_INFLUENCE_AREAS';

    private LoggerUtilInterface $loggerUtil;

    /** @var array<int, array<int, bool>> */
    private array $usedMaps = [];

    /** @var array<int, array<int, Map>> */
    private array $spreader = [];

    /** @var array<int, array<int, Map>> */
    private array $mapsByCoords = [];

    public function __construct(
        private readonly LayerRepositoryInterface $layerRepository,
        private readonly MapRepositoryInterface $mapRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTicks::VIEW_IDENTIFIER);

        // only Admins can trigger this
        if (!$game->isAdmin()) {
            $game->getInfo()->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $layerId = request::postIntFatal('layerid');
        $layer = $this->layerRepository->find($layerId);
        if ($layer === null) {
            $game->getInfo()->addInformationf("Layer mit ID %d nicht gefunden", $layerId);
            return;
        }

        $allMapWithSystem = $this->mapRepository->getAllWithSystem($layerId);
        $allMapWithoutSystem = $this->mapRepository->getAllWithoutSystem($layerId);

        $this->loadMapByCoords($allMapWithoutSystem);

        $allSystems = [];

        foreach ($allMapWithSystem as $map) {
            /** @var StarSystem */
            $system = $map->getSystem();
            $systemId = $system->getId();
            $allSystems[$systemId] = $system;

            $this->addSpreader($map, $systemId);
            $this->setInfluenceArea($map, $system);

            $this->spreadInCircle($map, $system, $layer);
        }

        $round = 0;
        while ($allMapWithoutSystem !== []) {
            $round++;
            $this->loggerUtil->log(sprintf('round: %d', $round));
            $this->shuffleAssoc($this->spreader);

            if ($this->spreader === []) {
                break;
            }

            foreach ($this->spreader as $influenceId => $spreaderPerSystem) {
                $this->loggerUtil->log(sprintf('influenceId: %d', $influenceId));
                $this->shuffleAssoc($spreaderPerSystem);

                foreach ($spreaderPerSystem as $id => $map) {
                    $neighbour = $this->getRandomFreeNeighbour($map, $layer);

                    if ($neighbour === null) {
                        unset($spreaderPerSystem[$id]);
                        continue;
                    }

                    $this->addSpreader($neighbour, $influenceId);
                    $this->setInfluenceArea($neighbour, $allSystems[$influenceId]);
                    unset($allMapWithoutSystem[$neighbour->getId()]);

                    break;
                }

                if ($allMapWithoutSystem === []) {
                    break;
                }

                if ($spreaderPerSystem === []) {
                    unset($this->spreader[$influenceId]);
                }
            }
        }


        $game->getInfo()->addInformation("Influence Areas wurden randomisiert verteilt");
    }

    private function spreadInCircle(Map $map, StarSystem $system, Layer $layer): void
    {
        $layerWidht = $layer->getWidth();
        $layerHeight = $layer->getHeight();

        $x = $map->getX();
        $y = $map->getY();

        $circleNeighbours = [];

        //top
        if ($y > 1 && !$this->isMapUsed($x, $y - 1)) {
            $circleNeighbours[] = $this->mapsByCoords[$x][$y - 1];
        }

        //top left
        if ($y > 1 && $x > 1 && !$this->isMapUsed($x - 1, $y - 1)) {
            $circleNeighbours[] = $this->mapsByCoords[$x - 1][$y - 1];
        }

        //top right
        if ($y > 1 && $x < $layerWidht && !$this->isMapUsed($x + 1, $y - 1)) {
            $circleNeighbours[] = $this->mapsByCoords[$x + 1][$y - 1];
        }

        //right
        if ($x < $layerWidht && !$this->isMapUsed($x + 1, $y)) {
            $circleNeighbours[] = $this->mapsByCoords[$x + 1][$y];
        }

        //bottom
        if ($y < $layerHeight && !$this->isMapUsed($x, $y + 1)) {
            $circleNeighbours[] = $this->mapsByCoords[$x][$y + 1];
        }

        //bottom left
        if ($y < $layerHeight && $x > 1 && !$this->isMapUsed($x - 1, $y + 1)) {
            $circleNeighbours[] = $this->mapsByCoords[$x - 1][$y + 1];
        }

        //bottom right
        if ($y < $layerHeight && $x < $layerWidht && !$this->isMapUsed($x + 1, $y + 1)) {
            $circleNeighbours[] = $this->mapsByCoords[$x + 1][$y + 1];
        }

        //left
        if ($x > 1 && !$this->isMapUsed($x - 1, $y)) {
            $circleNeighbours[] = $this->mapsByCoords[$x - 1][$y];
        }

        foreach ($circleNeighbours as $neighbour) {
            $this->addSpreader($neighbour, $system->getId());
            $this->setInfluenceArea($neighbour, $system);
        }
    }

    /**
     * @param array<int, mixed> $array
     */
    private function shuffleAssoc(array &$array): void
    {
        $keys = array_keys($array);

        shuffle($keys);

        $new = [];

        foreach ($keys as $key) {
            $new[$key] = $array[$key];
        }

        $array = $new;
    }

    /**
     * @param array<Map> $maps
     */
    private function loadMapByCoords(array $maps): void
    {
        foreach ($maps as $map) {
            $x = $map->getX();
            $y = $map->getY();

            if (!array_key_exists($x, $this->mapsByCoords)) {
                $this->mapsByCoords[$x] = [];
            }
            $this->mapsByCoords[$x][$y] = $map;
        }
    }

    private function getRandomFreeNeighbour(Map $map, Layer $layer): ?Map
    {
        $x = $map->getX();
        $y = $map->getY();

        $freeNeighbours = [];

        //top
        if ($y > 1 && !$this->isMapUsed($x, $y - 1)) {
            $freeNeighbours[] = $this->mapsByCoords[$x][$y - 1];
        }

        //right
        if ($x <  $layer->getWidth() && !$this->isMapUsed($x + 1, $y)) {
            $freeNeighbours[] = $this->mapsByCoords[$x + 1][$y];
        }

        //bottom
        if ($y < $layer->getHeight() && !$this->isMapUsed($x, $y + 1)) {
            $freeNeighbours[] = $this->mapsByCoords[$x][$y + 1];
        }

        //left
        if ($x > 1 && !$this->isMapUsed($x - 1, $y)) {
            $freeNeighbours[] = $this->mapsByCoords[$x - 1][$y];
        }

        if (empty($freeNeighbours)) {
            return null;
        } else {
            shuffle($freeNeighbours);

            return current($freeNeighbours);
        }
    }

    private function isMapUsed(int $x, int $y): bool
    {
        if (!array_key_exists($x, $this->usedMaps)) {
            return false;
        }
        return array_key_exists($y, $this->usedMaps[$x]);
    }

    private function setInfluenceArea(Map $map, StarSystem $system): void
    {
        $map->setInfluenceArea($system);
        $this->addUsedMap($map);
        $this->mapRepository->save($map);
    }

    private function addSpreader(Map $map, int $influenceId): void
    {
        $this->loggerUtil->log(sprintf('addSpreader - mapId: %d, influenceId: %d', $map->getId(), $influenceId));
        if (!array_key_exists($influenceId, $this->spreader)) {
            $this->spreader[$influenceId] = [];
        }
        $this->spreader[$influenceId][$map->getId()] = $map;
    }

    private function addUsedMap(Map $map): void
    {
        $x = $map->getX();
        $y = $map->getY();

        if (!array_key_exists($x, $this->usedMaps)) {
            $this->usedMaps[$x] = [];
        }
        $this->usedMaps[$x][$y] = true;
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
