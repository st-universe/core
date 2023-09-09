<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

use Stu\Component\Map\EncodedMapInterface;
use Stu\Orm\Entity\LayerInterface;

class SignaturePanelEntry implements VisualPanelElementInterface
{
    public const ONLY_BACKGROUND_IMAGE = 1;

    protected VisualPanelEntryData $data;

    private ?LayerInterface $layer;

    private EncodedMapInterface $encodedMap;

    private string $cssClass = 'lss';

    public function __construct(
        VisualPanelEntryData $data,
        ?LayerInterface $layer,
        EncodedMapInterface $encodedMap
    ) {
        $this->data = $data;
        $this->layer = $layer;
        $this->encodedMap = $encodedMap;
    }

    public function getLssCellData(): LssCellData
    {
        return new LssCellData(
            $this->getSystemBackgroundId(),
            $this->getFieldGraphicID(),
            $this->getMapGraphicPath(),
            $this->data->getShieldState(),
            $this->getSubspaceCode(),
            $this->getDisplayCount(),
        );
    }

    private function getFieldGraphicID(): ?int
    {
        $fieldId = $this->data->getMapfieldType();

        if ($fieldId === self::ONLY_BACKGROUND_IMAGE) {
            return null;
        }

        return $fieldId;
    }

    private function getSystemBackgroundId(): ?string
    {
        if ($this->data->getSystemId() === null) {
            return null;
        }

        return sprintf(
            '%02d%02d',
            $this->data->getPosY(),
            $this->data->getPosX()
        );
    }

    private function getMapGraphicPath(): ?string
    {
        $layer = $this->layer;
        if ($layer === null) {
            return null;
        }

        if ($layer->isEncoded()) {

            return $this->encodedMap->getEncodedMapPath(
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
