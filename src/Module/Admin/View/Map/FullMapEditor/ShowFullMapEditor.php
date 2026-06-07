<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map\FullMapEditor;

use request;
use Stu\Component\Map\MapEnum;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Admin\View\Map\LiveMap\ShowLiveMapImage;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\MapBorderType;
use Stu\Orm\Entity\MapFieldType;
use Stu\Orm\Entity\MapRegion;
use Stu\Orm\Entity\StarSystemType;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapBorderTypeRepositoryInterface;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;
use Stu\Orm\Repository\MapRegionRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemTypeRepositoryInterface;

final class ShowFullMapEditor implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ADMIN_FULL_MAP_EDITOR';

    public function __construct(
        private LayerRepositoryInterface $layerRepository,
        private MapRepositoryInterface $mapRepository,
        private MapFieldTypeRepositoryInterface $mapFieldTypeRepository,
        private StarSystemTypeRepositoryInterface $starSystemTypeRepository,
        private MapRegionRepositoryInterface $mapRegionRepository,
        private MapBorderTypeRepositoryInterface $mapBorderTypeRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        if (!$game->isAdmin()) {
            $game->getInfo()->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $layers = $this->layerRepository->findAllIndexed();
        $requestedLayerId = request::getInt('layerid', MapEnum::DEFAULT_LAYER);
        $layer = $requestedLayerId > 0
            ? ($layers[$requestedLayerId] ?? null)
            : current($layers);

        if (!$layer instanceof Layer) {
            $game->getInfo()->addInformation('Es existiert kein Kartenlayer');
            return;
        }

        $game->setPageTitle(_('Admin: Karteneditor Vollkarte'));
        $game->appendNavigationPart('/admin/?SHOW_ADMIN_FULL_MAP_EDITOR=1', _('Karteneditor Vollkarte'));
        $game->setTemplateFile('html/admin/fullMapEditor.twig');
        $game->setTemplateVar('LAYERS', $layers);
        $game->setTemplateVar('LAYER', $layer);
        $game->setTemplateVar('CELL_SIZE', ShowLiveMapImage::CELL_SIZE);
        $game->setTemplateVar('FIELD_TYPES', $this->getFieldTypes());
        $game->setTemplateVar('SYSTEM_TYPES', $this->getSystemTypes());
        $game->setTemplateVar('REGIONS', $this->getRegions($layer->getId()));
        $game->setTemplateVar('ADMIN_REGIONS', $this->getAdminRegions());
        $game->setTemplateVar('BORDER_TYPES', $this->getBorderTypes());
        $game->setTemplateVar('INFLUENCE_AREAS', $this->getInfluenceAreas($layer->getId()));
        $game->setTemplateVar('FIELD_EFFECTS', $this->getFieldEffects());
    }

    /** @return array<int, array{id: int, type: int, name: string}> */
    private function getFieldTypes(): array
    {
        $fieldTypes = array_values(array_filter(
            $this->mapFieldTypeRepository->findAll(),
            fn (MapFieldType $fieldType): bool => !$fieldType->getIsSystem()
        ));

        usort(
            $fieldTypes,
            fn (MapFieldType $a, MapFieldType $b): int => $a->getType() <=> $b->getType()
        );

        return array_map(
            fn (MapFieldType $fieldType): array => [
                'id' => $fieldType->getId(),
                'type' => $fieldType->getType(),
                'name' => $fieldType->getName()
            ],
            $fieldTypes
        );
    }

    /** @return array<int, array{id: int, description: string}> */
    private function getSystemTypes(): array
    {
        $systemTypes = array_values(array_filter(
            $this->starSystemTypeRepository->findAll(),
            fn (StarSystemType $systemType): bool => (bool) $systemType->getIsGenerateable()
        ));

        usort(
            $systemTypes,
            fn (StarSystemType $a, StarSystemType $b): int => $a->getId() <=> $b->getId()
        );

        return array_map(
            fn (StarSystemType $systemType): array => [
                'id' => $systemType->getId(),
                'description' => $systemType->getDescription()
            ],
            $systemTypes
        );
    }

    /** @return array<int, array{id: int, description: string}> */
    private function getRegions(int $layerId): array
    {
        $regions = [];
        foreach ($this->mapRegionRepository->findAll() as $region) {
            if ($region->getId() < 100 && $region->getDatabaseEntry() === null) {
                continue;
            }

            if (!$this->isRegionInLayer($region, $layerId)) {
                continue;
            }

            $regions[] = [
                'id' => $region->getId(),
                'description' => $region->getDescription()
            ];
        }

        return $regions;
    }

    /** @return array<int, array{id: int, description: string}> */
    private function getAdminRegions(): array
    {
        $regions = [];
        foreach ($this->mapRegionRepository->findAll() as $region) {
            if ($region->getId() >= 100) {
                continue;
            }

            $regions[] = [
                'id' => $region->getId(),
                'description' => $region->getDescription()
            ];
        }

        return $regions;
    }

    /** @return array<int, array{id: int, description: string, color: string}> */
    private function getBorderTypes(): array
    {
        return array_map(
            fn (MapBorderType $borderType): array => [
                'id' => $borderType->getId(),
                'description' => $borderType->getDescription(),
                'color' => $borderType->getColor()
            ],
            $this->mapBorderTypeRepository->findAll()
        );
    }

    /** @return array<int, int> */
    private function getInfluenceAreas(int $layerId): array
    {
        return [9999, ...$this->mapRepository->getUniqueInfluenceAreaIds($layerId)];
    }

    /** @return array<int, array{value: string, description: string}> */
    private function getFieldEffects(): array
    {
        return array_map(
            fn (FieldTypeEffectEnum $effect): array => [
                'value' => $effect->value,
                'description' => $effect->getDescription() ?? $effect->value
            ],
            FieldTypeEffectEnum::cases()
        );
    }

    private function isRegionInLayer(MapRegion $region, int $layerId): bool
    {
        $regionLayers = $region->getLayers();
        if ($regionLayers === null || trim($regionLayers) === '') {
            return true;
        }

        $layerIds = array_map('intval', explode(',', $regionLayers));
        return in_array($layerId, $layerIds, true);
    }
}
