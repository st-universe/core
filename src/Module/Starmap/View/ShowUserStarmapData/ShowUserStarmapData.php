<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowUserStarmapData;

use JsonException;
use request;
use Stu\Lib\Trait\LayerExplorationTrait;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Starmap\Lib\ExploreableStarMapInterface;
use Stu\Module\Starmap\Lib\StarmapUiFactoryInterface;
use Stu\Module\Starmap\View\ShowUserStarmapImage\ShowUserStarmapImage;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

final class ShowUserStarmapData implements ViewControllerInterface
{
    use LayerExplorationTrait;

    public const string VIEW_IDENTIFIER = 'SHOW_USER_STARMAP_DATA';

    private const int JSON_FLAGS = JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE;

    public function __construct(
        private LayerRepositoryInterface $layerRepository,
        private MapRepositoryInterface $mapRepository,
        private UserMapRepositoryInterface $userMapRepository,
        private StarmapUiFactoryInterface $starmapUiFactory
    ) {}

    /**
     * @throws JsonException
     */
    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $layerId = request::getIntFatal('layerid');
        $layer = $this->layerRepository->find($layerId);
        if (!$layer instanceof Layer) {
            header('HTTP/1.1 404 Not Found');
            exit;
        }

        $user = $game->getUser();
        if (!$this->hasSeen($user, $layer)) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $visibility = $this->getVisibility($user, $layer);
        $fields = $this->mapRepository->getUserStarmapFields($user->getId(), $layer->getId(), $visibility['full']);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        echo json_encode([
            'generatedAt' => time(),
            'cellSize' => ShowUserStarmapImage::CELL_SIZE,
            'imageVersion' => $visibility['version'],
            'fullyExplored' => $visibility['full'],
            'visibleRuns' => $visibility['runs'],
            'visibleFieldCount' => count($fields),
            'layer' => [
                'id' => $layer->getId(),
                'name' => $layer->getName(),
                'width' => $layer->getWidth(),
                'height' => $layer->getHeight()
            ],
            'fields' => array_map(
                fn (ExploreableStarMapInterface $field): array => $this->normalizeField($field, $layer),
                $fields
            )
        ], self::JSON_FLAGS);

        exit;
    }

    /**
     * @return array{full: bool, version: string, runs: array<int, array{y: int, startX: int, endX: int}>}
     */
    private function getVisibility(User $user, Layer $layer): array
    {
        if ($this->hasExplored($user, $layer)) {
            return [
                'full' => true,
                'version' => 'full',
                'runs' => $this->getFullLayerRuns($layer)
            ];
        }

        $runs = $this->userMapRepository->getVisibleMapFieldRuns($user->getId(), $layer->getId());

        return [
            'full' => false,
            'version' => $this->buildRunsVersion($runs),
            'runs' => $runs
        ];
    }

    /**
     * @return array<int, array{y: int, startX: int, endX: int}>
     */
    private function getFullLayerRuns(Layer $layer): array
    {
        $runs = [];
        for ($y = 1; $y <= $layer->getHeight(); $y++) {
            $runs[] = [
                'y' => $y,
                'startX' => 1,
                'endX' => $layer->getWidth()
            ];
        }

        return $runs;
    }

    /**
     * @param array<int, array{y: int, startX: int, endX: int}> $runs
     */
    private function buildRunsVersion(array $runs): string
    {
        $hash = hash_init('sha1');

        foreach ($runs as $run) {
            hash_update($hash, sprintf('%d:%d-%d;', $run['y'], $run['startX'], $run['endX']));
        }

        return hash_final($hash);
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeField(ExploreableStarMapInterface $field, Layer $layer): array
    {
        $item = $this->starmapUiFactory->createExplorableStarmapItem($field, $layer);
        $icon = $item->getIcon();

        return [
            'x' => $item->getCx(),
            'y' => $item->getCy(),
            'tooltip' => $item->getTooltip(),
            'icon' => $icon !== null ? sprintf('/assets/map/%s.png', $icon) : null,
            'databaseId' => $field->getMapped(),
            'hasTerritory' => $item->hasTerritory(),
            'territoryColor' => $this->extractColor($item->getTerritoryStyle()),
            'hasEffects' => $item->hasEffects(),
            'isImpassable' => $item->isImpassable()
        ];
    }

    private function extractColor(string $style): ?string
    {
        if (preg_match('/#[0-9a-fA-F]{6}/', $style, $matches) !== 1) {
            return null;
        }

        return $matches[0];
    }
}
