<?php

declare(strict_types=1);

use Stu\Component\Map\MapEnum;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

class VisualNavPanel
{
    private $ship;

    private $user;

    private LoggerUtilInterface $loggerUtil;

    private $isTachyonSystemActive;

    private $tachyonFresh;

    function __construct(
        ShipInterface $ship,
        UserInterface $user,
        LoggerUtilInterface $loggerUtil,
        bool $isTachyonSystemActive,
        bool $tachyonFresh
    ) {
        $this->ship = $ship;
        $this->user = $user;
        $this->loggerUtil = $loggerUtil;
        $this->isTachyonSystemActive = $isTachyonSystemActive;
        $this->tachyonFresh = $tachyonFresh;
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

        if ($this->user->getId() == 126) {
            return $container->get(ShipRepositoryInterface::class)->getSensorResultInnerSystemNew(
                $this->getShip()->getSystem()->getId(),
                $this->getShip()->getSx(),
                $this->getShip()->getSy(),
                $this->getShip()->getSensorRange(),
                $this->getShip()->getSubspaceState(),
                $this->user->getId()
            );
        } else {

            return $container->get(ShipRepositoryInterface::class)->getSensorResultInnerSystem(
                $this->getShip()->getSystem()->getId(),
                $this->getShip()->getSx(),
                $this->getShip()->getSy(),
                $this->getShip()->getSensorRange(),
                $this->getShip()->getSubspaceState(),
                $this->user->getId()
            );
        }
    }

    function loadLSS()
    {
        if ($this->loggerUtil->doLog()) {
            $startTime = microtime(true);
        }
        if ($this->getShip()->getSystem() !== null) {
            $result = $this->getInnerSystemResult();
        } else {
            $result = $this->getOuterSystemResult();
        }
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tloadLSS-query, seconds: %F", $endTime - $startTime));
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
            $entry = new VisualNavPanelEntry($data, $this->isTachyonSystemActive, $this->tachyonFresh);
            $entry->currentShipPosX = $cx;
            $entry->currentShipPosY = $cy;
            $rows[$y]->addEntry($entry);
        }
        if ($this->loggerUtil->doLog()) {
            $endTime = microtime(true);
            $this->loggerUtil->log(sprintf("\tloadLSS-loop, seconds: %F", $endTime - $startTime));
        }

        $this->rows = $rows;
    }

    function getHeadRow()
    {
        $cx = $this->getShip()->getPosX();
        $cy = $this->getShip()->getPosY();
        $range = $this->getShip()->getSensorRange();
        $min = $cx - $range;
        $max = $cx + $range;
        while ($min <= $max) {
            if ($min < 1) {
                $min++;
                continue;
            }
            if ($this->getShip()->getSystem() === null && $min > MapEnum::MAP_MAX_X) {
                break;
            }
            if ($this->getShip()->getSystem() !== null && $min > $this->getShip()->getSystem()->getMaxX()) {
                break;
            }
            $row[]['value'] = $min;
            $min++;
        }
        return $row;
    }
}
