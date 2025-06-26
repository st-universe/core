<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

use Stu\Lib\Map\VisualPanel\Layer\PanelLayers;
use Stu\Orm\Entity\StarSystem;

class SystemScanPanelEntry extends SignaturePanelEntry
{
    public function __construct(
        int $x,
        int $y,
        PanelLayers $layers,
        private StarSystem $system
    ) {
        parent::__construct($x, $y, $layers);
    }

    public function isClickAble(): bool
    {
        return true;
    }

    public function getOnClick(): string
    {
        return sprintf(
            'showSectorScanWindow(this, %d, %d, %d, %s);',
            $this->x,
            $this->y,
            $this->system->getId(),
            'false'
        );
    }
}
