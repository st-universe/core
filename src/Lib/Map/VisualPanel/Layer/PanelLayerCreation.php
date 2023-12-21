<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer;

use RuntimeException;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\PanelLayerDataProviderInterface;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceDataProviderFactoryInterface;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\Render\BorderLayerRenderer;
use Stu\Lib\Map\VisualPanel\Layer\Render\ColonyShieldLayerRenderer;
use Stu\Lib\Map\VisualPanel\Layer\Render\LayerRendererInterface;
use Stu\Lib\Map\VisualPanel\Layer\Render\MapLayerRenderer;
use Stu\Lib\Map\VisualPanel\Layer\Render\ShipCountLayerRenderer;
use Stu\Lib\Map\VisualPanel\Layer\Render\SubspaceLayerRenderer;
use Stu\Lib\Map\VisualPanel\Layer\Render\SystemLayerRenderer;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\ShipInterface;

final class PanelLayerCreation implements PanelLayerCreationInterface
{
    private EncodedMapInterface $encodedMap;

    private SubspaceDataProviderFactoryInterface $subspaceDataProviderFactory;

    /** @var array<int, PanelLayerDataProviderInterface> */
    private array $dataProviders;

    /** @var array<int, PanelLayerDataProviderInterface> */
    private array $specialDataProviders = [];

    /** @var array<int, LayerRendererInterface> */
    private array $layers = [];

    /** @param array<int, PanelLayerDataProviderInterface> $dataProviders */
    public function __construct(
        EncodedMapInterface $encodedMap,
        SubspaceDataProviderFactoryInterface $subspaceDataProviderFactory,
        array $dataProviders
    ) {
        $this->encodedMap = $encodedMap;
        $this->subspaceDataProviderFactory = $subspaceDataProviderFactory;
        $this->dataProviders = $dataProviders;
    }

    public function addSystemLayer(): PanelLayerCreationInterface
    {
        $this->layers[PanelLayerEnum::SYSTEM->value] = new SystemLayerRenderer();

        return $this;
    }

    public function addMapLayer(LayerInterface $layer): PanelLayerCreationInterface
    {
        $this->layers[PanelLayerEnum::MAP->value] = new MapLayerRenderer($layer, $this->encodedMap);

        return $this;
    }

    public function addColonyShieldLayer(): PanelLayerCreationInterface
    {
        $this->layers[PanelLayerEnum::COLONY_SHIELD->value] = new ColonyShieldLayerRenderer();

        return $this;
    }

    public function addSubspaceLayer(int $id, SubspaceLayerTypeEnum $type): PanelLayerCreationInterface
    {
        $this->layers[PanelLayerEnum::SUBSPACE_SIGNATURES->value] = new SubspaceLayerRenderer();
        $this->specialDataProviders[PanelLayerEnum::SUBSPACE_SIGNATURES->value] = $this->subspaceDataProviderFactory->getDataProvider($id, $type);

        return $this;
    }

    public function addShipCountLayer(
        bool $showCloakedEverywhere,
        ?ShipInterface $currentShip
    ): PanelLayerCreationInterface {
        $this->layers[PanelLayerEnum::SHIP_COUNT->value] = new ShipCountLayerRenderer($showCloakedEverywhere, $currentShip);

        return $this;
    }

    public function addBorderLayer(?ShipInterface $currentShip, ?bool $isOnShipLevel): PanelLayerCreationInterface
    {
        $this->layers[PanelLayerEnum::BORDER->value] = new BorderLayerRenderer($currentShip, $isOnShipLevel);

        return $this;
    }

    public function build(AbstractVisualPanel $panel): PanelLayers
    {
        $layers = $this->layers;
        $this->layers = [];

        return $this->createLayers($layers, $panel);
    }

    /** @param array<int, LayerRendererInterface> $layers */
    private function createLayers(array $layers, AbstractVisualPanel $panel): PanelLayers
    {
        $result = new PanelLayers($panel);

        foreach ($layers as $layerType => $renderer) {
            $result->addLayer(PanelLayerEnum::from($layerType), new PanelLayer($this->getDataProvider($layerType)->loadData(
                $panel->getBoundaries()
            ), $renderer));
        }

        return $result;
    }

    private function getDataProvider(int $layerType): PanelLayerDataProviderInterface
    {
        if (array_key_exists($layerType, $this->specialDataProviders)) {
            return $this->specialDataProviders[$layerType];
        }

        if (!array_key_exists($layerType, $this->dataProviders)) {
            throw new RuntimeException(sprintf('no layer data provider existent for type: %d', $layerType));
        }

        return $this->dataProviders[$layerType];
    }
}
