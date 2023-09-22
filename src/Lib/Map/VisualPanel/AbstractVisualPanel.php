<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

use Stu\Lib\Map\VisualPanel\VisualPanelRow;
use Stu\Module\Logging\LoggerUtilInterface;

abstract class AbstractVisualPanel
{
    protected LoggerUtilInterface $loggerUtil;

    /** @var null|array<int, VisualPanelRow> */
    private ?array $rows = null;

    /** @var null|array<int, VisualPanelRow> */
    private ?array $extendedrows = null;

    /** @var null|array<array{value: int}> */
    protected ?array $headRow = null;

    /** @var null|array<array{value: int}> */
    protected ?array $extendedheadRow = null;

    private ?float $viewport = null;

    private ?string $viewportPerColumn = null;

    private ?string $viewportForFont = null;

    public function __construct(
        LoggerUtilInterface $loggerUtil
    ) {
        $this->loggerUtil = $loggerUtil;
    }

    /**
     * @return array<array{value: int}>
     */
    public abstract function getHeadRow(): array;

    /**
     * @return array<int, VisualPanelRow>
     */
    public function getRows(): array
    {
        if ($this->rows === null) {
            $this->rows = $this->loadLSS();
        }
        return $this->rows;
    }

    public function getExtendedRows(): array
    {
        if ($this->extendedrows === null) {
            $this->extendedrows = $this->loadExtendedLSS();
        }
        return $this->extendedrows;
    }


    /**
     * @return array<VisualPanelRow>
     */
    protected abstract function loadLSS(): array;

    /**
     * @return array<VisualPanelRow>
     */
    protected abstract function loadExtendedLSS(): array;

    protected abstract function getPanelViewportPercentage(): int;

    private function getViewport(): float
    {
        if ($this->viewport === null) {
            $navPercentage = 100;
            $perColumn = $navPercentage / count($this->getHeadRow());
            $this->viewport = min($perColumn, 1.7);
        }
        return $this->viewport;
    }

    public function getViewportPerColumn(): string
    {
        if ($this->viewportPerColumn === null) {
            $this->viewportPerColumn = number_format($this->getViewport(), 1);
        }
        return $this->viewportPerColumn;
    }

    public function getViewportForFont(): string
    {
        if ($this->viewportForFont === null) {
            $this->viewportForFont = number_format($this->getViewport() / 2, 1);
        }
        return $this->viewportForFont;
    }
}
