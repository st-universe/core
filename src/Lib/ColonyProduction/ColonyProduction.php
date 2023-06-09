<?php

namespace Stu\Lib\ColonyProduction;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Stu\Orm\Entity\CommodityInterface;

/**
 * @Entity
 */
class ColonyProduction
{
    /** @Id @Column(type="integer") * */
    private int $colony_id = 0;

    /** @Id @Column(type="integer") * */
    private int $commodity_id = 0;

    /** @Column(type="integer") * */
    private int $production = 0;

    /** @Column(type="integer", nullable=true) * */
    private ?int $type = null;

    /** @Column(type="integer", nullable=true) * */
    private ?int $pc = null;

    /**
     * @var array<int, CommodityInterface>
     */
    private array $commodityCache;

    private int $preview = 0;
    private ?CommodityInterface $commodity = null;

    /**
     * @param array<int, CommodityInterface> $commodityCache
     */
    public function __construct(
        array $commodityCache,
        CommodityInterface $commodity,
        int $production
    ) {
        $this->commodityCache = $commodityCache;

        $this->commodity = $commodity;
        $this->production = $production;
    }

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function getCommodityType(): ?int
    {
        return $this->type;
    }

    public function getProduction(): int
    {
        if ($this->pc !== null) {
            return $this->production + $this->pc;
        }

        return $this->production;
    }

    public function setProduction(int $production): void
    {
        $this->production = $production;
    }

    public function getProductionDisplay(): string
    {
        if ($this->getProduction() <= 0) {
            return (string) $this->getProduction();
        }
        return '+' . $this->getProduction();
    }

    public function getCssClass(): string
    {
        if ($this->getProduction() < 0) {
            return 'negative';
        }
        if ($this->getProduction() > 0) {
            return 'positive';
        }
        return '';
    }

    public function lowerProduction(int $value): void
    {
        $this->setProduction($this->getProduction() - $value);
    }

    public function upperProduction(int $value): void
    {
        $this->setProduction($this->getProduction() + $value);
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
        if ($this->commodity === null) {
            $this->commodity =  $this->commodityCache[$this->getCommodityId()];
        }

        return $this->commodity;
    }
}
