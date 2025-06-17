<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer;

use Override;
use RuntimeException;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\PanelLayerDataProviderInterface;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Border\BorderDataProviderFactoryInterface;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Spacecraftcount\SpacecraftCountDataProviderFactoryInterface;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Spacecraftcount\SpacecraftCountLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceDataProviderFactoryInterface;
use Stu\Lib\Map\VisualPanel\Layer\DataProvider\Subspace\SubspaceLayerTypeEnum;
use Stu\Lib\Map\VisualPanel\Layer\Render\AnomalyLayerRenderer;
use Stu\Lib\Map\VisualPanel\Layer\Render\BorderLayerRenderer;
use Stu\Lib\Map\VisualPanel\Layer\Render\ColonyShieldLayerRenderer;
use Stu\Lib\Map\VisualPanel\Layer\Render\LayerRendererInterface;
use Stu\Lib\Map\VisualPanel\Layer\Render\MapLayerRenderer;
use Stu\Lib\Map\VisualPanel\Layer\Render\SpacecraftCountLayerRenderer;
use Stu\Lib\Map\VisualPanel\Layer\Render\SubspaceLayerRenderer;
use Stu\Lib\Map\VisualPanel\Layer\Render\SystemLayerRenderer;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\SpacecraftInterface;

final class PanelLayerCreation implements PanelLayerCreationInterface
{
    /** @var array<int, PanelLayerDataProviderInterface> */
    private array $specialDataProviders = [];

    /** @var array<int, LayerRendererInterface> */
    private array $layers = [];

    /** @var array<int> */
    public static array $skippedLayers = [];

    /** @param array<int, PanelLayerDataProviderInterface> $dataProviders */
    public function __construct(
        private EncodedMapInterface $encodedMap,
        private BorderDataProviderFactoryInterface $borderDataProviderFactory,
        private SpacecraftCountDataProviderFactoryInterface $shipcountDataProviderFactory,
        private SubspaceDataProviderFactoryInterface $subspaceDataProviderFactory,
        private array $dataProviders
    ) {}

    #[Override]
    public function addSystemLayer(): PanelLayerCreationInterface
    {
        $this->layers[PanelLayerEnum::SYSTEM->value] = new SystemLayerRenderer();

        return $this;
    }

    #[Override]
    public function addMapLayer(LayerInterface $layer): PanelLayerCreationInterface
    {
        $this->layers[PanelLayerEnum::MAP->value] = new MapLayerRenderer($layer, $this->encodedMap);

        return $this;
    }

    #[Override]
    public function addColonyShieldLayer(): PanelLayerCreationInterface
    {
        $this->layers[PanelLayerEnum::COLONY_SHIELD->value] = new ColonyShieldLayerRenderer();

        return $this;
    }

    #[Override]
    public function addSubspaceLayer(int $id, SubspaceLayerTypeEnum $type): PanelLayerCreationInterface
    {
        $this->layers[PanelLayerEnum::SUBSPACE_SIGNATURES->value] = new SubspaceLayerRenderer();
        $this->specialDataProviders[PanelLayerEnum::SUBSPACE_SIGNATURES->value] = $this->subspaceDataProviderFactory->getDataProvider($id, $type);

        return $this;
    }

    #[Override]
    public function addShipCountLayer(
        bool $showCloakedEverywhere,
        ?SpacecraftInterface $currentSpacecraft,
        SpacecraftCountLayerTypeEnum $type,
        int $id
    ): PanelLayerCreationInterface {
        $this->layers[PanelLayerEnum::SPACECRAFT_COUNT->value] = new SpacecraftCountLayerRenderer($showCloakedEverywhere, $currentSpacecraft);
        $this->specialDataProviders[PanelLayerEnum::SPACECRAFT_COUNT->value] = $this->shipcountDataProviderFactory->getDataProvider($id, $type);

        return $this;
    }

    #[Override]
    public function addBorderLayer(?SpacecraftWrapperInterface $currentWrapper, ?bool $isOnShipLevel): PanelLayerCreationInterface
    {
        $this->layers[PanelLayerEnum::BORDER->value] = new BorderLayerRenderer($currentWrapper, $isOnShipLevel);
        $this->specialDataProviders[PanelLayerEnum::BORDER->value] = $this->borderDataProviderFactory->getDataProvider($currentWrapper, $isOnShipLevel);

        return $this;
    }

    #[Override]
    public function addAnomalyLayer(): PanelLayerCreationInterface
    {
        $this->layers[PanelLayerEnum::ANOMALIES->value] = new AnomalyLayerRenderer();

        return $this;
    }

    #[Override]
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

            if (!in_array($layerType, self::$skippedLayers)) {
                $result->addLayer(PanelLayerEnum::from($layerType), new PanelLayer($this->getDataProvider($layerType)->loadData(
                    $panel->getBoundaries()
                ), $renderer));
            }
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
