<?php

namespace Stu\Lib\ColonyProduction;

use Stu\Orm\Entity\CommodityInterface;

class ColonyProduction
{
    private int $preview = 0;

    public function __construct(private CommodityInterface $commodity, private int $production, private ?int $pc)
    {
    }

    public function getCommodityId(): int
    {
        return $this->commodity->getId();
    }

    public function getCommodityType(): int
    {
        return $this->commodity->getType();
    }

    public function getProduction(): int
    {
        if ($this->pc !== null) {
            return $this->production + $this->pc;
        }

        return $this->production;
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
        $this->setProduction($this->production - $value);
    }

    public function upperProduction(int $value): void
    {
        $this->setProduction($this->production + $value);
    }

    private function setProduction(int $value): void
    {
        $this->production = $value;
    }

    public function setPreviewProduction(int $value): void
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
        return $this->commodity;
    }
}
