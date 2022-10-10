<?php

namespace Stu\Lib\ColonyProduction;

use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

class ColonyProduction
{

    private $data = null;

    function __construct(&$data = array())
    {
        $this->data = $data;

        if (!empty($data)) {
            $this->data['gc'] += $this->data['pc'];
        }
    }

    public function getCommodityId()
    {
        return $this->data['commodity_id'];
    }

    function setCommodityId($value)
    {
        $this->data['commodity_id'] = $value;
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

    public function getCommodity(): CommodityInterface
    {
        // @todo refactor
        global $container;

        return $container->get(CommodityRepositoryInterface::class)->find((int) $this->getCommodityId());
    }
}
