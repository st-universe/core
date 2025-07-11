<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer;

use RuntimeException;
use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\Layer\Render\LayerRendererInterface;
use Stu\Lib\Map\VisualPanel\PanelAttributesInterface;

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

    public function renderCell(int $x, int $y, PanelAttributesInterface $panel): string
    {
        if (!array_key_exists($x, $this->data)) {
            throw new RuntimeException('array index not available');
        }

        return $this->renderer->render($this->data[$x][$y], $panel);
    }
}
