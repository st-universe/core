<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\CreateInfluenceAreas;

use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class CreateInfluenceAreas implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_INFLUENCE_AREAS';

    private MapRepositoryInterface $mapRepository;

    private LoggerUtilInterface $loggerUtil;

    private $usedMaps = [];
    private $spreader = [];
    private $mapsByCoords = [];

    public function __construct(
        MapRepositoryInterface $mapRepository,
        LoggerUtilInterface $loggerUtil
    ) {
        $this->mapRepository = $mapRepository;
        $this->loggerUtil = $loggerUtil;
    }

    public function handle(GameControllerInterface $game): void
    {
        $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);

        $game->setView(ShowTicks::VIEW_IDENTIFIER);

        // only Admins can trigger this
        if (!$game->getUser()->isAdmin()) {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $allMapWithSystem = $this->mapRepository->getAllWithSystem();
        $allMapWithoutSystem = $this->mapRepository->getAllWithoutSystem();

        $this->loadMapByCoords($allMapWithoutSystem);

        $allSystems = [];

        foreach ($allMapWithSystem as $map) {
            $system = $map->getSystem();
            $systemId = $system->getId();
            $allSystems[$systemId] = $system;
            $this->addSpreader($map, $systemId);

            $this->setInfluenceArea($map, $system);
        }

        $round = 0;
        while (!empty($allMapWithoutSystem)) {
            $round++;
            $this->loggerUtil->log(sprintf('round: %d', $round));
            shuffle($this->spreader);

            foreach ($this->spreader as $influenceId => $spreaderPerSystem) {
                $this->loggerUtil->log(sprintf('influenceId: %d', $influenceId));
                shuffle($spreaderPerSystem);

                foreach ($spreaderPerSystem as $map) {
                    $neighbour = $this->getRandomFreeNeighbour($map);

                    if ($neighbour === null) {
                        unset($spreaderPerSystem[$map->getId()]);
                        continue;
                    }

                    $this->addSpreader($neighbour, $influenceId);
                    $this->setInfluenceArea($neighbour, $allSystems[$influenceId]);
                    unset($allMapWithoutSystem[$neighbour->getId()]);

                    break;
                }

                if (empty($allMapWithoutSystem)) {
                    break;
                }

                if (empty($spreaderPerSystem)) {
                    unset($this->spreader[$influenceId]);
                }
            }
        }


        $game->addInformation("Influence Areas wurden randomisiert verteilt");
    }

    private function loadMapByCoords(array $maps): void
    {
        foreach ($maps as $map) {
            $x = $map->getCx();
            $y = $map->getCy();

            if (!array_key_exists($x, $this->mapsByCoords)) {
                $this->mapsByCoords[$x] = [];
            }
            $this->mapsByCoords[$x][$y] = $map;
        }
    }

    private function getRandomFreeNeighbour(MapInterface $map): ?MapInterface
    {
        $x = $map->getCx();
        $y = $map->getCy();

        $freeNeighbours = [];

        //top
        if ($y > 1 && !$this->isMapUsed($x, $y - 1)) {
            $freeNeighbours[] = $this->mapsByCoords[$x][$y - 1];
        }

        //right
        if ($x < 120 && !$this->isMapUsed($x + 1, $y)) {
            $freeNeighbours[] = $this->mapsByCoords[$x + 1][$y];
        }

        //bottom
        if ($y < 120 && !$this->isMapUsed($x, $y + 1)) {
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

    private function setInfluenceArea(MapInterface $map, StarSystemInterface $system): void
    {
        $map->setInfluenceArea($system);
        $this->addUsedMap($map);
        $this->mapRepository->save($map);
    }

    private function addSpreader(MapInterface $map, int $influenceId): void
    {
        $this->loggerUtil->log(sprintf('addSpreader - mapId: %d, influenceId: %d', $map->getId(), $influenceId));
        if (!array_key_exists($influenceId, $this->spreader)) {
            $this->spreader[$influenceId] = [];
        }
        $this->spreader[$influenceId][$map->getId()] = $map;
    }

    private function addUsedMap(MapInterface $map): void
    {
        $x = $map->getCx();
        $y = $map->getCy();

        if (!array_key_exists($x, $this->usedMaps)) {
            $this->usedMaps[$x] = [];
        }
        $this->usedMaps[$x][$y] = true;
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
