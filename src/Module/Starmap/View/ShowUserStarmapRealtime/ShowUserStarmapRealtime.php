<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowUserStarmapRealtime;

use Doctrine\ORM\EntityManagerInterface;
use JsonException;
use Noodlehaus\ConfigInterface;
use request;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Component\Realtime\RealtimeChannels;
use Stu\Component\Realtime\RealtimeRedisFactory;
use Stu\Component\Realtime\StarmapRealtimeTokenFactory;
use Stu\Lib\Trait\LayerExplorationTrait;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Starmap\Lib\StarmapRelations;
use Stu\Module\Starmap\Lib\StarmapSensorCoverage;
use Stu\Module\Starmap\Lib\StarmapSensorData;
use Stu\Module\Station\View\ShowSensorScan\ShowSensorScan;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Throwable;

final class ShowUserStarmapRealtime implements ViewControllerInterface
{
    use LayerExplorationTrait;

    public const string VIEW_IDENTIFIER = 'SHOW_USER_STARMAP_REALTIME';

    private const int JSON_FLAGS = JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE;
    private const int TOKEN_TTL_SECONDS = 300;
    private const int COVERAGE_TTL_SECONDS = 360;
    private const int REFRESH_AFTER_SECONDS = 240;

    public function __construct(
        private LayerRepositoryInterface $layerRepository,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private StarmapRealtimeTokenFactory $tokenFactory,
        private RealtimeRedisFactory $redisFactory,
        private ConfigInterface $config,
        private AllianceJobManagerInterface $allianceJobManager,
        private StarmapSensorData $sensorData,
        private StarmapSensorCoverage $sensorCoverage,
        private StarmapRelations $relations,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private RealtimeChannels $channels,
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * @throws JsonException
     */
    #[\Override]
    public function handle(ViewControllerContext $game): void
    {
        $response = $this->getResponse($game);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo $response;
        exit;
    }

    private function getResponse(ViewControllerContext $game): string
    {
        $layerId = request::getIntFatal('layerid');
        $layer = $this->layerRepository->find($layerId);
        if (!$layer instanceof Layer) {
            header('HTTP/1.1 404 Not Found');
            exit();
        }

        $user = $game->getUser();
        if (!$this->hasSeen($user, $layer)) {
            header('HTTP/1.1 403 Forbidden');
            exit();
        }

        $this->entityManager->flush();
        $this->entityManager->commit();
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        $coverageGeneration = $this->getCoverageGeneration($layerId);
        $alliance = $user->getAlliance();
        $canSeeAllianceShips = $alliance !== null
        && $this->allianceJobManager->hasUserPermission(
            $user,
            $alliance,
            AllianceJobPermissionEnum::VIEW_SHIPS
        );
        $ranges = $this->spacecraftRepository->getUserStarmapRealtimeSensorRanges(
            $user->getId(),
            $layer->getId()
        );
        $coverage = $this->sensorCoverage->buildSensorCoverage($layer, $ranges);

        $sensorSpacecrafts = $ranges === []
            ? []
            : $this->spacecraftRepository->getUserStarmapRealtimeSpacecrafts($user->getId(), $layer->getId());
        $relationContext = $this->relations->getRelationContext($user);
        $sharedSpacecrafts =
            $relationContext['sharedUserIds'] === [] && $relationContext['sharedAllianceIds'] === []
                ? []
                : $this->spacecraftRepository->getStarmapRealtimeSharedSpacecrafts(
                    $layer->getId(),
                    array_keys($relationContext['sharedUserIds']),
                    array_keys($relationContext['sharedAllianceIds'])
                );
        $spacecrafts = array_values(array_column(
            [...$sensorSpacecrafts, ...$sharedSpacecrafts],
            null,
            'id'
        ));
        $spacecrafts = array_values(array_filter(array_map(
            fn (array $row): ?array => $this->sensorData->normalizeVisibleSpacecraft(
                $row,
                $user,
                $canSeeAllianceShips,
                $relationContext,
                $coverage
            ),
            $spacecrafts
        )));

        $dubiousFields = [];
        foreach ($spacecrafts as $spacecraft) {
            if ($spacecraft['isDubiousSignature'] ?? false) {
                $dubiousFields[$spacecraft['x'] . ':' . $spacecraft['y']] = ['x' => $spacecraft['x'], 'y' => $spacecraft['y']];
            }
        }
        $spacecrafts = array_values(array_filter($spacecrafts, static fn (array $spacecraft): bool => !($spacecraft['isDubiousSignature'] ?? false)));

        $sources = [];
        foreach ($ranges as $range) {
            $source = $this->spacecraftRepository->find((int) $range['source_id']);
            if ($source === null) {
                continue;
            }
            $sourceCoverage = $this->sensorCoverage->buildSensorCoverage($layer, [$range]);
            $sources[] = [
                'id' => $source->getId(),
                'name' => $this->sensorData->parseBbCodeText($source->getName()),
                'eps' => $this->spacecraftWrapperFactory->wrapSpacecraft($source)->getEpsSystemData()?->getEps() ?? 0,
                'runs' => $sourceCoverage['runs']
            ];
        }

        $this->cacheCoverage(
            $user->getId(),
            $layer->getId(),
            $coverage,
            json_encode($sources, self::JSON_FLAGS),
            json_encode(array_map(static fn (array $spacecraft): array => [
                'id' => $spacecraft['id'],
                'x' => $spacecraft['x'],
                'y' => $spacecraft['y'],
                'isOwn' => $spacecraft['isOwn'],
                'isFriendly' => $spacecraft['isFriendly'],
                'isCloakedSignature' => $spacecraft['isCloakedSignature'] ?? false
            ], $spacecrafts), self::JSON_FLAGS),
            json_encode($spacecrafts, self::JSON_FLAGS),
            json_encode(array_values($dubiousFields), self::JSON_FLAGS),
            $coverageGeneration
        );

        return json_encode([
            'generatedAt' => time(),
            'sensorSources' => $sources,
            'dubiousFields' => array_values($dubiousFields),
            'scanEnergyCost' => ShowSensorScan::ENERGY_COST_SECTOR_SCAN,
            'token' => $this->tokenFactory->create(
                $user->getId(),
                $layer->getId(),
                self::TOKEN_TTL_SECONDS,
                $this->sensorData->getRealtimeTokenClaims($alliance?->getId(), $canSeeAllianceShips, $relationContext)
            ),
            'tokenTtl' => self::TOKEN_TTL_SECONDS,
            'refreshAfter' => self::REFRESH_AFTER_SECONDS,
            'webSocketUrl' => $this->getWebSocketUrl(),
            'sensorRangeCount' => count($ranges),
            'sensorCoverageRuns' => $coverage['runs'],
            'sensorCoverageFieldCount' => $coverage['fieldCount'],
            'sensorTachyonFieldCount' => $coverage['tachyonFieldCount'],
            'spacecrafts' => array_map(static fn (array $spacecraft): array => $spacecraft['isOwn'] || $spacecraft['isFriendly']
                ? $spacecraft : array_replace($spacecraft, ['id' => -random_int(1, 2 ** 48 - 1)]), $spacecrafts)
        ], self::JSON_FLAGS);
    }

    private function getCoverageGeneration(int $layerId): ?int
    {
        try {
            $redis = $this->redisFactory->create();
            return $redis === null ? null : (int) $redis->get($this->channels->starmapCoverageGenerationKey($layerId));
        } catch (Throwable) {
            return null;
        }
    }

    private function getWebSocketUrl(): string
    {
        $url = getenv('STU_REALTIME_WEBSOCKET_URL');
        if (is_string($url) && trim($url) !== '') {
            return trim($url);
        }

        $url = $this->config->get('realtime.webSocketUrl');

        return is_string($url) && trim($url) !== '' ? trim($url) : '/realtime/starmap';
    }

    /**
     * @param array{
     *     runs: array<int, array{y: int, startX: int, endX: int}>,
     *     tachyonRuns: array<int, array{y: int, startX: int, endX: int}>,
     *     sourceCount: int,
     *     fieldCount: int,
     *     tachyonFieldCount: int,
     *     visibleKeys: array<string, true>,
     *     tachyonKeys: array<string, true>
     * } $coverage
     */
    private function cacheCoverage(int $userId, int $layerId, array $coverage, string $sources, string $contacts, string $spacecrafts, string $dubiousFields, ?int $generation): void
    {
        if ($generation === null) {
            return;
        }
        try {
            $redis = $this->redisFactory->create();
            if ($redis === null) {
                return;
            }

            $redis->setex(
                $this->channels->starmapCoverageKey($userId, $layerId),
                self::COVERAGE_TTL_SECONDS,
                json_encode([
                    'runs' => $coverage['runs'],
                    'tachyonRuns' => $coverage['tachyonRuns'],
                    'sourceCount' => $coverage['sourceCount'],
                    'fieldCount' => $coverage['fieldCount'],
                    'tachyonFieldCount' => $coverage['tachyonFieldCount'],
                    'generation' => $generation,
                    'snapshotVersion' => bin2hex(random_bytes(8)),
                    'sources' => json_decode($sources, true, 512, JSON_THROW_ON_ERROR),
                    'contacts' => json_decode($contacts, true, 512, JSON_THROW_ON_ERROR),
                    'spacecrafts' => json_decode($spacecrafts, true, 512, JSON_THROW_ON_ERROR),
                    'dubiousFields' => json_decode($dubiousFields, true, 512, JSON_THROW_ON_ERROR)
                ], self::JSON_FLAGS)
            );
        } catch (Throwable) {
            return;
        }
    }

}
