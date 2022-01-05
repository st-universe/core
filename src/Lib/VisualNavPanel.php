<?php

declare(strict_types=1);

use Stu\Component\Map\MapEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

class VisualNavPanel
{
    private $ship;

    private $showOuterMap;

    private $user;

    private LoggerUtilInterface $loggerUtil;

    private $isTachyonSystemActive;

    private $tachyonFresh;

    private $system;

    function __construct(
        ShipInterface $ship,
        UserInterface $user,
        LoggerUtilInterface $loggerUtil,
        bool $isTachyonSystemActive,
        bool $tachyonFresh,
        StarSystemInterface $system = null
    ) {
        $this->ship = $ship;
        $this->user = $user;
        $this->loggerUtil = $loggerUtil;
        $this->isTachyonSystemActive = $isTachyonSystemActive;
        $this->tachyonFresh = $tachyonFresh;
        $this->system = $system;

        $this->showOuterMap = $this->getShip()->getSystem() === null
            || $this->getShip()->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_SENSOR;
    }

    function getShip()
    {
        return $this->ship;
    }

    private $rows = null;

    function getRows()
    {
        if ($this->rows === null) {
            $this->loadLSS();
        }
        return $this->rows;
    }

    function getOuterSystemResult()
    {
        $cx = $this->getShip()->getCX();
        $cy = $this->getShip()->getCY();
        $range = $this->getShip()->getSensorRange();

        // @todo refactor
        global $container;
        $repo = $container->get(UserMapRepositoryInterface::class);

        if ($this->user->getMapType() === MapEnum::MAPTYPE_INSERT) {
            $repo->insertMapFieldsForUser(
                $this->user->getId(),
                $cx,
                $cy,
                $range
            );
        } else {
            $repo->deleteMapFieldsForUser(
                $this->user->getId(),
                $cx,
                $cy,
                $range
            );
        }

        return $container->get(ShipRepositoryInterface::class)->getSensorResultOuterSystem(
            $cx,
            $cy,
            $range,
            $this->getShip()->getSubspaceState(),
            $this->user->getId()
        );
    }

    function getInnerSystemResult()
    {
        // @todo refactor
        global $container;

        return $container->get(ShipRepositoryInterface::class)->getSensorResultInnerSystem(
            $this->getShip(),
            $this->user->getId(),
            $this->system
        );
    }

    function loadLSS()
    {
        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        if ($this->system === null && $this->showOuterMap) {
            $result = $this->getOuterSystemResult();
        } else {
            $result = $this->getInnerSystemResult();
        }
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            //$this->loggerUtil->log(sprintf("\tloadLSS-query, seconds: %F", $endTime - $startTime));
        }

        $cx = $this->getShip()->getPosX();
        $cy = $this->getShip()->getPosY();
        $y = 0;

        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }

        foreach ($result as $data) {
            if ($data['posy'] < 1) {
                continue;
            }
            if ($data['posy'] != $y) {
                $y = $data['posy'];
                $rows[$y] = new VisualNavPanelRow;
                $entry = new VisualNavPanelEntry();
                $entry->row = $y;
                $entry->setCSSClass('th');
                $rows[$y]->addEntry($entry);
            }
            if ($this->user->getId() === 126 && $data['posx'] == 19 && $data['posy'] == 14) {
                $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);
                $this->loggerUtil->log(print_r($data, true));
            }
            $entry = new VisualNavPanelEntry(
                $data,
                $this->isTachyonSystemActive,
                $this->tachyonFresh,
                $this->getShip(),
                $this->system
            );
            if ($this->system === null) {
                $entry->currentShipPosX = $cx;
                $entry->currentShipPosY = $cy;
            }
            $rows[$y]->addEntry($entry);
        }
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            //$this->loggerUtil->log(sprintf("\tloadLSS-loop, seconds: %F", $endTime - $startTime));
        }

        $this->rows = $rows;
    }

    private $headRow = null;

    function getHeadRow()
    {
        if ($this->headRow === null) {
            $cx = $this->showOuterMap ? $this->getShip()->getCx() : $this->getShip()->getPosX();
            $range = $this->getShip()->getSensorRange();
            $min = $this->system ? 1 : $cx - $range;
            $max = $this->system ? $this->system->getMaxX() : $cx + $range;
            while ($min <= $max) {
                if ($min < 1) {
                    $min++;
                    continue;
                }
                if ($this->system === null) {
                    if ($this->showOuterMap && $min > MapEnum::MAP_MAX_X) {
                        break;
                    }
                    if (!$this->showOuterMap && $min > $this->getShip()->getSystem()->getMaxX()) {
                        break;
                    }
                }
                $row[]['value'] = $min;
                $min++;
            }

            $this->headRow = $row;
        }
        return $this->headRow;
    }


    private $viewport;
    private $viewportPerColumn;
    private $viewportForFont;

    private function getViewport()
    {
        if (!$this->viewportPerColumn) {
            $navPercentage = ($this->system === null && $this->getShip()->isBase()) ? 50 : 33;
            $perColumn = $navPercentage / count($this->getHeadRow());
            $this->viewport = min($perColumn, 1.7);
        }
        return $this->viewport;
    }

    function getViewportPerColumn()
    {
        if (!$this->viewportPerColumn) {
            $this->viewportPerColumn = number_format($this->getViewport(), 1);
        }
        return $this->viewportPerColumn;
    }

    function getViewportForFont()
    {
        if (!$this->viewportForFont) {
            $this->viewportForFont = number_format($this->getViewport() / 2, 1);
        }
        return $this->viewportForFont;
    }
}
