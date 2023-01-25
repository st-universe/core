<?php

namespace Stu\Lib\ColonyProduction;

use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

class ColonyProduction
{
    /** @var int */
    private $preview = 0;

    private $data = null;

    function __construct(&$data = array())
    {
        $this->data = $data;

        if (!empty($data)) {
            $this->data['gc'] += $this->data['pc'];
        }
    }

    public function getCommodityId(): int
    {
        return $this->data['commodity_id'];
    }

    function setCommodityId($value): void
    {
        $this->data['commodity_id'] = $value;
    }

    function getProduction(): int
    {
        return $this->data['gc'];
    }

    function getProductionDisplay(): string
    {
        if ($this->getProduction() <= 0) {
            return (string) $this->getProduction();
        }
        return '+' . $this->getProduction();
    }

    function getCssClass(): string
    {
        if ($this->getProduction() < 0) {
            return 'negative';
        }
        if ($this->getProduction() > 0) {
            return 'positive';
        }
        return '';
    }

    /**
     * @param int $value
     */
    function lowerProduction($value): void
    {
        $this->setProduction($this->getProduction() - $value);
    }

    /**
     * @param int $value
     */
    function upperProduction($value): void
    {
        $this->setProduction($this->getProduction() + $value);
    }

    /**
     * @param int $value
     */
    function setProduction($value): void
    {
        $this->data['gc'] = $value;
    }

    /**
     * @param int $value
     */
    public function setPreviewProduction($value): void
    {
        $this->preview = $value;
    }

    public function getPreviewProduction(): int
    {
        return $this->preview;
    }

    public function getPreviewProductionDisplay(): string
    {
        if ($this->getPreviewProduction() <= 0) {
            return (string) $this->getPreviewProduction();
        }
        return '+' . $this->getPreviewProduction();
    }

    public function getPreviewProductionCss(): string
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
