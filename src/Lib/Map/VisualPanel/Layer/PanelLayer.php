<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer;

use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\Layer\Render\LayerRendererInterface;

final class PanelLayer
{
    /** @var array<int, array<int, CellDataInterface>> */
    private array $data = [];

    /**
     * @param array<CellDataInterface> $array
     */
    public function __construct(array $array, private LayerRendererInterface $renderer)
    {
        foreach ($array as $data) {
            $this->data[$data->getPosX()][$data->getPosY()] = $data;
        }
    }

    public function renderCell(int $x, int $y, AbstractVisualPanel $panel): string
    {
        /**
         * 
         if ($this->renderer instanceof SystemLayerRenderer) {
             // throw new RuntimeException(print_r($this->data, true));
         }
         */

        return $this->renderer->render($this->data[$x][$y], $panel);
    }
}
