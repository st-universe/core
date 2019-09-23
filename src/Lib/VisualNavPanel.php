<?php

declare(strict_types=1);

use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

class VisualNavPanel
{
    private $ship;

    private $user;

    function __construct(ShipInterface $ship, UserInterface $user)
    {
        $this->ship = $ship;
        $this->user = $user;
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

        if ($this->user->getMapType() == MAPTYPE_INSERT) {
            DB()->query("INSERT IGNORE INTO stu_user_map (id,user_id,cx,cy,map_id) SELECT uuid()," .$this->user ->getId() . " as user_id,cx,cy,id as map_id FROM stu_map WHERE cx BETWEEN " . ($cx - $range) . " AND " . ($cx + $range) . " AND cy BETWEEN " . ($cy - $range) . " AND " . ($cy + $range));
        } else {
            DB()->query("DELETE FROM stu_user_map WHERE user_id=" .$this->user ->getId() . " AND cx BETWEEN " . ($cx - $range) . " AND " . ($cx + $range) . " AND cy BETWEEN " . ($cy - $range) . " AND " . ($cy + $range) . "");
        }
        // @todo refactor
        global $container;

        return $container->get(ShipRepositoryInterface::class)->getSensorResultOuterSystem(
            $cx,
            $cy,
            $range
        );
    }

    function getInnerSystemResult()
    {
        // @todo refactor
        global $container;

        return $container->get(ShipRepositoryInterface::class)->getSensorResultInnerSystem(
            $this->getShip()->getSystem()->getId(),
            $this->getShip()->getSx(),
            $this->getShip()->getSy(),
            $this->getShip()->getSensorRange()
        );
    }

    function loadLSS()
    {
        if ($this->getShip()->getSystemsId() > 0) {
            $result = $this->getInnerSystemResult();
        } else {
            $result = $this->getOuterSystemResult();
        }
        $cx = $this->getShip()->getPosX();
        $cy = $this->getShip()->getPosY();
        $y = 0;
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
            $entry = new VisualNavPanelEntry($data);
            $entry->currentShipPosX = $cx;
            $entry->currentShipPosY = $cy;
            $rows[$y]->addEntry($entry);
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
            if ($this->getShip()->getSystemsId() == 0 && $min > MAP_MAX_X) {
                break;
            }
            if ($this->getShip()->getSystemsId() > 0 && $min > $this->getShip()->getSystem()->getMaxX()) {
                break;
            }
            $row[]['value'] = $min;
            $min++;
        }
        return $row;
    }

}