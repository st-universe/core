<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

use Override;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayerCreationInterface;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayers;
use Stu\Module\Logging\LoggerUtilInterface;

abstract class AbstractVisualPanel implements PanelAttributesInterface
{
    protected PanelLayers $layers;

    private ?PanelBoundaries $boundaries = null;

    /** @var null|array<int, VisualPanelRow> */
    private ?array $rows = null;

    private ?float $viewport = null;

    private ?string $heightAndWidth = null;

    private ?string $fontSize = null;

    public function __construct(
        protected PanelLayerCreationInterface $panelLayerCreation,
        protected LoggerUtilInterface $loggerUtil
    ) {}

    abstract protected function createBoundaries(): PanelBoundaries;

    abstract protected function loadLayers(): void;

    abstract protected function getEntryCallable(): callable;

    abstract protected function getPanelViewportPercentage(): int;

    /**
     * @return array<int>
     */
    public function getHeadRow(): array
    {
        return $this->getBoundaries()->getColumnRange();
    }

    /**
     * @return array<int, VisualPanelRow>
     */
    public function getRows(): array
    {
        if ($this->rows === null) {
            $this->loadLayers();
            $this->rows = $this->loadPanelRows();
        }
        return $this->rows;
    }

    /**
     * @return array<VisualPanelRow>
     */
    private function loadPanelRows(): array
    {
        $rows = [];

        foreach ($this->getBoundaries()->getRowRange() as $y) {

            $rows[$y] = new VisualPanelRow($y);

            $callable = $this->getEntryCallable();

            foreach ($this->getBoundaries()->getColumnRange() as $x) {
                $rows[$y]->addEntry($callable($x, $y));
            }
        }

        return $rows;
    }

    public function getBoundaries(): PanelBoundaries
    {
        if ($this->boundaries === null) {
            $this->boundaries = $this->createBoundaries();
        }

        return $this->boundaries;
    }

    #[Override]
    public function getHeightAndWidth(): string
    {
        if ($this->heightAndWidth === null) {

            $viewPortScale = number_format($this->getViewport(), 1);
            $this->heightAndWidth = sprintf('height: %1$svw; width: %1$svw;', $viewPortScale);
        }
        return $this->heightAndWidth;
    }

    #[Override]
    public function getFontSize(): string
    {
        if ($this->fontSize === null) {
            $this->fontSize =  sprintf('font-size: %svw;', number_format($this->getViewport() / 2, 1));
        }
        return $this->fontSize;
    }

    private function getViewport(): float
    {
        if ($this->viewport === null) {
            $navPercentage = $this->getPanelViewportPercentage();
            $perColumn = $navPercentage / count($this->getHeadRow());
            $this->viewport = min($perColumn, 1.7);
        }
        return $this->viewport;
    }
}
