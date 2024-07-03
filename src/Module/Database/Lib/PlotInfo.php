<?php

declare(strict_types=1);

namespace Stu\Module\Database\Lib;

final class PlotInfo
{
    public function __construct(public string $method, public string $lineColor = 'purple', public string $fillColor = '#aa4dec@0.5', public ?string $legend = null)
    {
    }
}
