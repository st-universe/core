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
use Stu\Lib\Map\VisualPanel\LssBlockade\LssBlockadeGridFactory;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Spacecraft;

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
        private readonly EncodedMapInterface $encodedMap,
        private readonly BorderDataProviderFactoryInterface $borderDataProviderFactory,
        private readonly SpacecraftCountDataProviderFactoryInterface $shipcountDataProviderFactory,
        private readonly SubspaceDataProviderFactoryInterface $subspaceDataProviderFactory,
        private readonly LssBlockadeGridFactory $lssBlockadeGridFactory,
        private readonly array $dataProviders
    ) {}

    #[Override]
    public function addSystemLayer(): PanelLayerCreationInterface
    {
        $this->layers[PanelLayerEnum::SYSTEM->value] = new SystemLayerRenderer();

        return $this;
    }

    #[Override]
    public function addMapLayer(Layer $layer): PanelLayerCreationInterface
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
        $this->layers[PanelLayerEnum::SUBSPACE_SIGNATURES->value] = new SubspaceLayerRenderer(PanelLayerEnum::SUBSPACE_SIGNATURES->value);
        $this->specialDataProviders[PanelLayerEnum::SUBSPACE_SIGNATURES->value] = $this->subspaceDataProviderFactory->getDataProvider($id, $type);

        return $this;
    }
    #[Override]
    public function addSpacecraftSignatureLayer(int $spacecraftId, ?int $rumpId = null): PanelLayerCreationInterface
    {
        $this->layers[PanelLayerEnum::SPACECRAFT_SIGNATURE->value] = new SubspaceLayerRenderer(PanelLayerEnum::SPACECRAFT_SIGNATURE->value, true);
        $this->specialDataProviders[PanelLayerEnum::SPACECRAFT_SIGNATURE->value] = $this->subspaceDataProviderFactory->getDataProvider($spacecraftId, SubspaceLayerTypeEnum::SPACECRAFT_ONLY, $rumpId);

        return $this;
    }

    #[Override]
    public function addShipCountLayer(
        bool $showCloakedEverywhere,
        ?Spacecraft $currentSpacecraft,
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
    public function build(AbstractVisualPanel $panel, ?Location $observerLocation = null): PanelLayers
    {
        $layers = $this->layers;
        $this->layers = [];

        return $this->createLayers($layers, $panel, $observerLocation);
    }

    /** @param array<int, LayerRendererInterface> $layers */
    private function createLayers(array $layers, AbstractVisualPanel $panel, ?Location $observerLocation): PanelLayers
    {
        $lssBlockadeGrid = $observerLocation !== null ? $this->lssBlockadeGridFactory->createLssBlockadeGrid($observerLocation, $panel) : null;
        $result = new PanelLayers($panel, $lssBlockadeGrid);

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
