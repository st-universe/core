<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

final class PlotInfo
{
    public string $method;
    public string $lineColor;
    public string $fillColor;
    public string $legend;

    function __construct(
        string $method,
        string $lineColor = 'purple',
        string $fillColor = '#aa4dec@0.5',
        string $legend = null
    ) {
        $this->method = $method;
        $this->lineColor = $lineColor;
        $this->fillColor = $fillColor;
        $this->legend = $legend;
    }
}
