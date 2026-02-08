<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

final class GraphInfo
{
    public function __construct(
        public string $title,
        /** @var PlotInfo[] */
        private array $plotInfos,
        public bool $yAxisStartAtZero = false
    ) {}

    /**
     * @return PlotInfo[]
     */
    public function getPlotInfos(): array
    {
        return $this->plotInfos;
    }
}
