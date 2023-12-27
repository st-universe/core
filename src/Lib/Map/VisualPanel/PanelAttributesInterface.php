<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

interface PanelAttributesInterface
{
    public function getHeightAndWidth(): string;

    public function getFontSize(): string;
}
