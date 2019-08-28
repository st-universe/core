<?php

declare(strict_types=1);

class VisualNavPanel
{

    private $ship;

    private $user;

    function __construct(ShipData $ship, UserData $user)
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
            DB()->query("INSERT IGNORE INTO stu_user_map (user_id,cx,cy,map_id) SELECT " .$this->user ->getId() . " as user_id,cx,cy,id as map_id FROM stu_map WHERE cx BETWEEN " . ($cx - $range) . " AND " . ($cx + $range) . " AND cy BETWEEN " . ($cy - $range) . " AND " . ($cy + $range));
        } else {
            DB()->query("DELETE FROM stu_user_map WHERE user_id=" .$this->user ->getId() . " AND cx BETWEEN " . ($cx - $range) . " AND " . ($cx + $range) . " AND cy BETWEEN " . ($cy - $range) . " AND " . ($cy + $range) . "");
        }

        return DB()->query("SELECT cx as posx,cy as posy,
			(SELECT count(*) FROM stu_ships WHERE cx=posx and cy=posy and cloak=0) as shipcount,
			(SELECT count(*) FROM stu_ships where cx=posx AND cy=posy AND cloak=1) as cloakcount,
			(SELECT type FROM stu_map_ftypes where id=field_id) as type,
			field_id,bordertype_id,region_id FROM stu_map WHERE cx BETWEEN " . ($cx - $range) . " AND " . ($cx + $range) . " AND cy BETWEEN " . ($cy - $range) . " AND " . ($cy + $range) . " ORDER BY cy,cx");
        return DB()->query("SELECT cx as posx,cy as posy,field_id,bordertype_id,region_id,shipcount,cloakcount,type FROM stu_views_lss WHERE cx IN (" . $r_x . ") AND cy IN (" . $r_y . ") ORDER BY cy,cx");
    }

    function getInnerSystemResult()
    {
        $cx = $this->getShip()->getSX();
        $cy = $this->getShip()->getSY();
        $range = $this->getShip()->getSensorRange();

        return DB()->query("SELECT sx as posx,sy as posy,systems_id as sysid,
			(SELECT count(*) FROM stu_ships WHERE sx=posx and sy=posy and cloak=0 AND systems_id=sysid) as shipcount,
			(SELECT count(*) FROM stu_ships where sx=posx AND sy=posy AND cloak=1 AND systems_id=sysid) as cloakcount,
			(SELECT type FROM stu_map_ftypes where id=field_id) as type,
			field_id FROM stu_sys_map WHERE systems_id=" . $this->getShip()->getSystemsId() . " AND sx BETWEEN " . ($cx - $range) . " AND " . ($cx + $range) . " AND sy BETWEEN " . ($cy - $range) . " AND " . ($cy + $range) . " ORDER BY sy,sx");
    }

    function loadLSS()
    {
        if ($this->getShip()->isInSystem()) {
            $result = $this->getInnerSystemResult();
        } else {
            $result = $this->getOuterSystemResult();
        }
        $cx = $this->getShip()->getPosX();
        $cy = $this->getShip()->getPosY();
        $y = 0;
        while ($data = mysqli_fetch_assoc($result)) {
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
            if (!$this->getShip()->isInSystem() && $min > MAP_MAX_X) {
                break;
            }
            if ($this->getShip()->isInSystem() && $min > $this->getShip()->getSystem()->getMaxX()) {
                break;
            }
            $row[]['value'] = $min;
            $min++;
        }
        return $row;
    }

}