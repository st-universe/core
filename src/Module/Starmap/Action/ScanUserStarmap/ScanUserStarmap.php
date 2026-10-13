<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Action\ScanUserStarmap;

use request;
use Stu\Lib\Trait\LayerExplorationTrait;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\ViewContextMetadataTypeEnum;
use Stu\Module\Starmap\Lib\StarmapSensorData;
use Stu\Module\Starmap\View\ShowUserStarmapScan\ShowUserStarmapScan;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Module\Station\View\ShowSensorScan\ShowSensorScan;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class ScanUserStarmap implements ActionControllerInterface
{
    use LayerExplorationTrait;

    public const string ACTION_IDENTIFIER = 'B_SCAN_USER_STARMAP';

    public function __construct(
        private LayerRepositoryInterface $layerRepository,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private StationLoaderInterface $stationLoader,
        private StarmapSensorData $sensorData
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowUserStarmapScan::VIEW_IDENTIFIER);
        $context->setViewContext(ViewContextMetadataTypeEnum::STARMAP_SCAN_RESULT, $this->scan($context));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }

    private function scan(ActionControllerContext $context): string
    {
        $user = $context->getUser();
        $layer = $this->layerRepository->find(request::postInt('layerid'));
        $sourceId = request::postInt('sourceid');
        $x = request::postInt('x');
        $y = request::postInt('y');

        if (!$layer instanceof Layer || !$this->hasSeen($user, $layer)) {
            return json_encode(['success' => false, 'message' => 'Kartenebene nicht verfügbar'], JSON_THROW_ON_ERROR);
        }

        $ranges = $this->spacecraftRepository->getUserStarmapRealtimeSensorRanges($user->getId(), $layer->getId());
        $sourceRanges = array_values(array_filter($ranges, static fn (array $range): bool => (int) $range['source_id'] === $sourceId));
        if ($sourceRanges === []) {
            return json_encode(['success' => false, 'message' => 'Sensorquelle nicht verfügbar'], JSON_THROW_ON_ERROR);
        }

        $wrapper = $this->stationLoader->getWrapperByIdAndUser($sourceId, $user->getId(), true);
        $ranges = $this->spacecraftRepository->getUserStarmapRealtimeSensorRanges($user->getId(), $layer->getId());
        $sourceRanges = array_values(array_filter($ranges, static fn (array $range): bool => (int) $range['source_id'] === $sourceId));
        $coverage = $this->sensorData->buildSensorCoverage($layer, $sourceRanges);
        $key = sprintf('%d:%d', $x, $y);
        if (!isset($coverage['visibleKeys'][$key])) {
            return json_encode(['success' => false, 'message' => 'Feld liegt außerhalb des Sensorbereichs'], JSON_THROW_ON_ERROR);
        }

        $eps = $wrapper->getEpsSystemData();
        if ($eps === null || $eps->getEps() < ShowSensorScan::ENERGY_COST_SECTOR_SCAN) {
            return json_encode([
                'success' => false,
                'message' => sprintf('Nicht genügend Energie vorhanden (%d EPS benötigt)', ShowSensorScan::ENERGY_COST_SECTOR_SCAN),
                'sourceId' => $sourceId,
                'eps' => $eps?->getEps() ?? 0
            ], JSON_THROW_ON_ERROR);
        }

        $relations = $this->sensorData->getRelationContext($user);
        $contacts = [];
        foreach ($this->spacecraftRepository->getUserStarmapRealtimeSpacecrafts($user->getId(), $layer->getId()) as $row) {
            if ((int) $row['x'] !== $x || (int) $row['y'] !== $y) {
                continue;
            }

            $contact = $this->sensorData->normalizeVisibleSpacecraft($row, $user, false, $relations, $coverage, true);
            if ($contact !== null && !$contact['isOwn'] && !$contact['isFriendly']) {
                $contacts[] = $contact;
            }
        }

        $eps->lowerEps(ShowSensorScan::ENERGY_COST_SECTOR_SCAN)->update();

        return json_encode([
            'success' => true,
            'layerId' => $layer->getId(),
            'scannedAt' => time(),
            'x' => $x,
            'y' => $y,
            'sourceId' => $sourceId,
            'eps' => $eps->getEps(),
            'spacecrafts' => $contacts
        ], JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE);
    }
}
