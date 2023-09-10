<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

use Stu\Component\Map\EncodedMapInterface;
use Stu\Orm\Entity\LayerInterface;

class SignaturePanelEntry implements VisualPanelElementInterface
{
    protected VisualPanelEntryData $data;

    private ?LayerInterface $layer;

    private ?EncodedMapInterface $encodedMap;

    private string $cssClass = 'lss';

    public function __construct(
        VisualPanelEntryData $data,
        ?LayerInterface $layer,
        ?EncodedMapInterface $encodedMap
    ) {
        $this->data = $data;
        $this->layer = $layer;
        $this->encodedMap = $encodedMap;
    }

    public function getCellData(): MapCellData|SystemCellData
    {
        if ($this->data->getSystemId() === null) {
            return new MapCellData(
                $this->getMapGraphicPath(),
                $this->getSubspaceCode(),
                $this->getDisplayCount(),
            );
        }
        return new SystemCellData(
            $this->data->getPosX(),
            $this->data->getPosY(),
            $this->data->getMapfieldType(),
            $this->data->getShieldState(),
            $this->getSubspaceCode(),
            $this->getDisplayCount(),
        );
    }

    private function getMapGraphicPath(): ?string
    {
        $layer = $this->layer;
        if ($layer === null) {
            return null;
        }

        $encodedMap = $this->encodedMap;
        if ($layer->isEncoded() && $encodedMap !== null) {

            return $encodedMap->getEncodedMapPath(
                $this->data->getMapfieldType(),
                $layer
            );
        }

        return sprintf('%d/%d.png', $layer->getId(), $this->data->getMapfieldType());
    }

    private function getSubspaceCode(): ?string
    {
        if (!$this->data->isSubspaceCodeAvailable()) {
            return null;
        }

        return sprintf(
            '%d%d%d%d',
            $this->getCode($this->data->getDirection1Count()),
            $this->getCode($this->data->getDirection2Count()),
            $this->getCode($this->data->getDirection3Count()),
            $this->getCode($this->data->getDirection4Count())
        );
    }

    private function getCode(int $shipCount): int
    {
        //TODO use constant values
        if ($shipCount == 0) {
            return 0;
        }
        if ($shipCount == 1) {
            return 1;
        }
        if ($shipCount < 6) {
            return 2;
        }
        if ($shipCount < 11) {
            return 3;
        }
        if ($shipCount < 21) {
            return 4;
        }

        return 5;
    }

    protected function getDisplayCount(): ?string
    {
        if ($this->data->getShipCount() > 0) {
            return (string) $this->data->getShipCount();
        }

        return null;
    }

    public function getBorder(): string
    {
        // default border style
        return '#2d2d2d';
    }

    public function getCSSClass(): string
    {
        return $this->cssClass;
    }

    public function isRowIndex(): bool
    {
        return false;
    }
}
