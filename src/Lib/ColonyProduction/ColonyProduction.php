<?php

namespace Stu\Lib\ColonyProduction;

use ColonyData;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

class ColonyProduction
{

    private $data = null;

    function __construct(&$data = array())
    {
        $this->data = $data;
        $this->data['gc'] += $this->data['pc'];
    }

    public function getGoodId()
    {
        return $this->data['goods_id'];
    }

    function setGoodId($value)
    {
        $this->data['goods_id'] = $value;
    }

    function getProduction()
    {
        return $this->data['gc'];
    }

    function getProductionDisplay()
    {
        if ($this->getProduction() <= 0) {
            return $this->getProduction();
        }
        return '+' . $this->getProduction();
    }

    function getCssClass()
    {
        if ($this->getProduction() < 0) {
            return 'negative';
        }
        if ($this->getProduction() > 0) {
            return 'positive';
        }
    }

    function lowerProduction($value)
    {
        $this->setProduction($this->getProduction() - $value);
    }

    function upperProduction($value)
    {
        $this->setProduction($this->getProduction() + $value);
    }

    function setProduction($value)
    {
        $this->data['gc'] = $value;
    }

    private $preview = false;

    public function setPreviewProduction($value)
    {
        $this->preview = $value;
    }

    public function getPreviewProduction()
    {
        return $this->preview;
    }

    public function getPreviewProductionDisplay()
    {
        if ($this->getPreviewProduction() <= 0) {
            return $this->getPreviewProduction();
        }
        return '+' . $this->getPreviewProduction();
    }

    public function getPreviewProductionCss()
    {
        if ($this->getPreviewProduction() < 0) {
            return 'negative';
        }
        return 'positive';
    }

    public function getGood(): CommodityInterface
    {
        // @todo refactor
        global $container;

        return $container->get(CommodityRepositoryInterface::class)->find((int) $this->getGoodId());
    }

    static function getProductionByColony(ColonyData $col)
    {
        $result = DB()->query('SELECT id as goods_id,id as global_goods_id,(SELECT SUM(a.count) FROM stu_buildings_goods as a LEFT JOIN stu_colonies_fielddata as b USING(buildings_id) WHERE a.goods_id=global_goods_id AND b.colonies_id=' . $col->getId() . ' AND b.aktiv=1) as gc,(SELECT count FROM stu_planets_goods WHERE goods_id=global_goods_id AND planet_classes_id=' . $col->getColonyClass() . ') as pc FROM stu_goods GROUP BY id HAVING gc!=0 OR pc!=0');
        $ret = array();
        while ($data = mysqli_fetch_assoc($result)) {
            $ret[(int)$data['goods_id']] = new ColonyProduction($data);
        }
        return $ret;
    }
}
