<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map\FullMapEditor;

use JsonException;
use request;
use Stu\Module\Admin\View\Map\LiveMap\ShowLiveMapImage;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class ShowFullMapEditorData implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ADMIN_FULL_MAP_EDITOR_DATA';

    private const int JSON_FLAGS = JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE;

    public function __construct(
        private LayerRepositoryInterface $layerRepository,
        private MapRepositoryInterface $mapRepository
    ) {}

    /**
     * @throws JsonException
     */
    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        if (!$game->isAdmin()) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $layerId = request::getIntFatal('layerid');
        $layer = $this->layerRepository->find($layerId);
        if (!$layer instanceof Layer) {
            header('HTTP/1.1 404 Not Found');
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        echo json_encode([
            'generatedAt' => time(),
            'cellSize' => ShowLiveMapImage::CELL_SIZE,
            'layer' => [
                'id' => $layer->getId(),
                'name' => $layer->getName(),
                'width' => $layer->getWidth(),
                'height' => $layer->getHeight()
            ],
            'fields' => array_map(
                fn (array $row): array => self::normalizeFieldRow($row),
                $this->mapRepository->getAdminFullMapEditorFields($layer->getId())
            )
        ], self::JSON_FLAGS);

        exit;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    public static function normalizeFieldRow(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'x' => (int) $row['x'],
            'y' => (int) $row['y'],
            'fieldTypeId' => (int) $row['field_type_id'],
            'fieldTypeGraphic' => (int) $row['field_type_graphic'],
            'fieldName' => (string) $row['field_name'],
            'isSystemField' => (bool) $row['is_system'],
            'passable' => (bool) $row['passable'],
            'effects' => self::decodeEffects($row['effects'] !== null ? (string) $row['effects'] : null),
            'systemTypeId' => self::positiveIntOrNull($row['system_type_id'] ?? null),
            'systemTypeName' => self::stringOrNull($row['system_type_name'] ?? null),
            'systemId' => self::positiveIntOrNull($row['system_id'] ?? null),
            'systemName' => self::stringOrNull($row['system_name'] ?? null),
            'influenceAreaId' => self::positiveIntOrNull($row['influence_area_id'] ?? null),
            'influenceAreaName' => self::stringOrNull($row['influence_area_name'] ?? null),
            'borderTypeId' => self::positiveIntOrNull($row['border_type_id'] ?? null),
            'borderColor' => self::normalizeColor(self::stringOrNull($row['border_color'] ?? null)),
            'borderDescription' => self::stringOrNull($row['border_description'] ?? null),
            'regionId' => self::positiveIntOrNull($row['region_id'] ?? null),
            'regionName' => self::stringOrNull($row['region_name'] ?? null),
            'adminRegionId' => self::positiveIntOrNull($row['admin_region_id'] ?? null),
            'adminRegionName' => self::stringOrNull($row['admin_region_name'] ?? null)
        ];
    }

    /** @return array<int, string> */
    public static function decodeEffects(?string $effects): array
    {
        if ($effects === null || trim($effects) === '') {
            return [];
        }

        $decoded = json_decode($effects, true);
        if (!is_array($decoded)) {
            return [];
        }

        return array_values(array_filter($decoded, 'is_string'));
    }

    private static function positiveIntOrNull(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $intValue = (int) $value;
        return $intValue > 0 ? $intValue : null;
    }

    private static function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $stringValue = trim((string) $value);
        return $stringValue !== '' ? $stringValue : null;
    }

    private static function normalizeColor(?string $color): ?string
    {
        if ($color === null) {
            return null;
        }

        $trimmed = trim($color);
        if (preg_match('/^#?[0-9a-fA-F]{6}$/', $trimmed) !== 1) {
            return null;
        }

        return $trimmed[0] === '#' ? $trimmed : '#' . $trimmed;
    }
}
