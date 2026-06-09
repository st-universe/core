<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowUserStarmapRealtime;

use JBBCode\Parser;
use JsonException;
use Noodlehaus\ConfigInterface;
use request;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\Realtime\RealtimeChannels;
use Stu\Component\Realtime\RealtimeRedisFactory;
use Stu\Component\Realtime\StarmapRealtimeTokenFactory;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Lib\Map\VisualPanel\LssBlockade\LssBlockadeGrid;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Lib\Trait\LayerExplorationTrait;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
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

    /** @var array<string, string> */
    private array $bbCodeTextCache = [];

    /** @var array<string, string> */
    private array $bbCodeHtmlCache = [];

    public function __construct(
        private LayerRepositoryInterface $layerRepository,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private StarmapRealtimeTokenFactory $tokenFactory,
        private RealtimeRedisFactory $redisFactory,
        private Parser $bbCodeParser,
        private ConfigInterface $config,
        private AllianceJobManagerInterface $allianceJobManager,
        private AllianceRelationRepositoryInterface $allianceRelationRepository,
        private ContactRepositoryInterface $contactRepository,
        private MapRepositoryInterface $mapRepository
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

        $alliance = $user->getAlliance();
        $canSeeAllianceShips = $alliance !== null
            && $this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::VIEW_SHIPS);
        $ranges = $this->spacecraftRepository->getUserStarmapRealtimeSensorRanges($user->getId(), $layer->getId());
        $coverage = $this->buildSensorCoverage($layer, $ranges);
        $this->cacheCoverage($user->getId(), $layer->getId(), $coverage);

        $spacecrafts = $ranges === []
            ? []
            : $this->spacecraftRepository->getUserStarmapRealtimeSpacecrafts($user->getId(), $layer->getId());
        $relationContext = $this->getRelationContext($user);
        $spacecrafts = array_values(array_filter(array_map(
            fn (array $row): ?array => $this->normalizeVisibleSpacecraft($row, $user, $canSeeAllianceShips, $relationContext, $coverage),
            $spacecrafts
        )));

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        echo json_encode([
            'generatedAt' => time(),
            'token' => $this->tokenFactory->create(
                $user->getId(),
                $layer->getId(),
                self::TOKEN_TTL_SECONDS,
                $this->getRealtimeTokenClaims($alliance?->getId(), $canSeeAllianceShips, $relationContext)
            ),
            'tokenTtl' => self::TOKEN_TTL_SECONDS,
            'refreshAfter' => self::REFRESH_AFTER_SECONDS,
            'webSocketUrl' => $this->getWebSocketUrl(),
            'sensorRangeCount' => count($ranges),
            'sensorCoverageRuns' => $coverage['runs'],
            'sensorCoverageFieldCount' => $coverage['fieldCount'],
            'sensorTachyonFieldCount' => $coverage['tachyonFieldCount'],
            'spacecrafts' => $spacecrafts
        ], self::JSON_FLAGS);

        exit;
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
    private function cacheCoverage(int $userId, int $layerId, array $coverage): void
    {
        try {
            $redis = $this->redisFactory->create();
            if ($redis === null) {
                return;
            }

            $redis->setex(
                RealtimeChannels::starmapCoverageKey($userId, $layerId),
                self::COVERAGE_TTL_SECONDS,
                json_encode([
                    'runs' => $coverage['runs'],
                    'tachyonRuns' => $coverage['tachyonRuns'],
                    'sourceCount' => $coverage['sourceCount'],
                    'fieldCount' => $coverage['fieldCount'],
                    'tachyonFieldCount' => $coverage['tachyonFieldCount']
                ], self::JSON_FLAGS)
            );
        } catch (Throwable) {
            return;
        }
    }

    /**
     * @param array<int, array<string, mixed>> $ranges
     * @return array{
     *     runs: array<int, array{y: int, startX: int, endX: int}>,
     *     tachyonRuns: array<int, array{y: int, startX: int, endX: int}>,
     *     sourceCount: int,
     *     fieldCount: int,
     *     tachyonFieldCount: int,
     *     visibleKeys: array<string, true>,
     *     tachyonKeys: array<string, true>
     * }
     */
    private function buildSensorCoverage(Layer $layer, array $ranges): array
    {
        $visibleKeys = [];
        $tachyonKeys = [];

        if ($ranges === []) {
            return $this->buildCoverageResult($visibleKeys, $tachyonKeys, 0);
        }

        $blockers = $this->getLssBlockadeKeys($layer, $ranges);

        foreach ($ranges as $range) {
            $sourceX = (int) $range['x'];
            $sourceY = (int) $range['y'];
            $sensorRange = max(0, (int) $range['sensor_range']);
            $tachyonRange = $this->getEffectiveTachyonRange($sensorRange, max(0, (int) ($range['tachyon_range'] ?? 0)));
            $maxRange = $sensorRange;

            if ($maxRange <= 0) {
                continue;
            }

            $minX = max(1, $sourceX - $maxRange);
            $maxX = min($layer->getWidth(), $sourceX + $maxRange);
            $minY = max(1, $sourceY - $maxRange);
            $maxY = min($layer->getHeight(), $sourceY + $maxRange);

            $grid = new LssBlockadeGrid($minX, $maxX, $minY, $maxY, $sourceX, $sourceY);
            foreach ($blockers as $blockerKey => $_value) {
                [$blockerX, $blockerY] = array_map('intval', explode(':', $blockerKey));
                if ($blockerX < $minX || $blockerX > $maxX || $blockerY < $minY || $blockerY > $maxY) {
                    continue;
                }
                $grid->setBlocked($blockerX, $blockerY);
            }

            for ($y = $minY; $y <= $maxY; $y++) {
                for ($x = $minX; $x <= $maxX; $x++) {
                    if (!$grid->isVisible($x, $y)) {
                        continue;
                    }

                    $distanceX = abs($x - $sourceX);
                    $distanceY = abs($y - $sourceY);
                    $key = $this->fieldKey($x, $y);

                    if ($distanceX <= $sensorRange && $distanceY <= $sensorRange) {
                        $visibleKeys[$key] = true;
                    }
                    if ($tachyonRange > 0 && $distanceX <= $tachyonRange && $distanceY <= $tachyonRange) {
                        $tachyonKeys[$key] = true;
                    }
                }
            }
        }

        return $this->buildCoverageResult($visibleKeys, $tachyonKeys, count($ranges));
    }

    /**
     * @param array<int, array<string, mixed>> $ranges
     * @return array<string, true>
     */
    private function getLssBlockadeKeys(Layer $layer, array $ranges): array
    {
        $minX = $layer->getWidth();
        $maxX = 1;
        $minY = $layer->getHeight();
        $maxY = 1;

        foreach ($ranges as $range) {
            $sourceX = (int) $range['x'];
            $sourceY = (int) $range['y'];
            $maxRange = max(0, (int) $range['sensor_range']);
            if ($maxRange <= 0) {
                continue;
            }

            $minX = min($minX, max(1, $sourceX - $maxRange));
            $maxX = max($maxX, min($layer->getWidth(), $sourceX + $maxRange));
            $minY = min($minY, max(1, $sourceY - $maxRange));
            $maxY = max($maxY, min($layer->getHeight(), $sourceY + $maxRange));
        }

        if ($minX > $maxX || $minY > $maxY) {
            return [];
        }

        $boundaries = new PanelBoundaries($minX, $maxX, $minY, $maxY, $layer);
        $keys = [];
        foreach ($this->mapRepository->getLssBlockadeLocations($boundaries) as $entry) {
            $effects = $entry['effects'] ?? null;
            if (!is_string($effects) || !str_contains($effects, FieldTypeEffectEnum::LSS_BLOCKADE->value)) {
                continue;
            }
            $keys[$this->fieldKey((int) $entry['x'], (int) $entry['y'])] = true;
        }

        return $keys;
    }

    private function getEffectiveTachyonRange(int $sensorRange, int $tachyonRange): int
    {
        return min($sensorRange, $tachyonRange);
    }

    /**
     * @param array<string, true> $visibleKeys
     * @param array<string, true> $tachyonKeys
     * @return array{
     *     runs: array<int, array{y: int, startX: int, endX: int}>,
     *     tachyonRuns: array<int, array{y: int, startX: int, endX: int}>,
     *     sourceCount: int,
     *     fieldCount: int,
     *     tachyonFieldCount: int,
     *     visibleKeys: array<string, true>,
     *     tachyonKeys: array<string, true>
     * }
     */
    private function buildCoverageResult(array $visibleKeys, array $tachyonKeys, int $sourceCount): array
    {
        return [
            'runs' => $this->buildRunsFromKeys($visibleKeys),
            'tachyonRuns' => $this->buildRunsFromKeys($tachyonKeys),
            'sourceCount' => $sourceCount,
            'fieldCount' => count($visibleKeys),
            'tachyonFieldCount' => count($tachyonKeys),
            'visibleKeys' => $visibleKeys,
            'tachyonKeys' => $tachyonKeys
        ];
    }

    /**
     * @param array<string, true> $keys
     * @return array<int, array{y: int, startX: int, endX: int}>
     */
    private function buildRunsFromKeys(array $keys): array
    {
        $rows = [];
        foreach (array_keys($keys) as $key) {
            [$x, $y] = array_map('intval', explode(':', $key));
            $rows[$y][] = $x;
        }
        ksort($rows);

        $runs = [];
        foreach ($rows as $y => $xs) {
            sort($xs);
            $startX = $xs[0];
            $lastX = $startX;
            foreach (array_slice($xs, 1) as $x) {
                if ($x === $lastX + 1) {
                    $lastX = $x;
                    continue;
                }
                $runs[] = ['y' => (int) $y, 'startX' => $startX, 'endX' => $lastX];
                $startX = $lastX = $x;
            }
            $runs[] = ['y' => (int) $y, 'startX' => $startX, 'endX' => $lastX];
        }

        return $runs;
    }

    private function fieldKey(int $x, int $y): string
    {
        return sprintf('%d:%d', $x, $y);
    }

    /**
     * @param array<string, mixed> $row
     * @param array{friendlyUserIds: array<int, true>, enemyUserIds: array<int, true>, friendlyAllianceIds: array<int, true>, enemyAllianceIds: array<int, true>} $relationContext
     * @param array{
     *     runs: array<int, array{y: int, startX: int, endX: int}>,
     *     tachyonRuns: array<int, array{y: int, startX: int, endX: int}>,
     *     sourceCount: int,
     *     fieldCount: int,
     *     tachyonFieldCount: int,
     *     visibleKeys: array<string, true>,
     *     tachyonKeys: array<string, true>
     * } $coverage
     * @return null|array<string, mixed>
     */
    private function normalizeVisibleSpacecraft(
        array $row,
        User $user,
        bool $canSeeAllianceShips,
        array $relationContext,
        array $coverage
    ): ?array {
        $key = $this->fieldKey((int) $row['x'], (int) $row['y']);
        $isOwn = (int) $row['user_id'] === $user->getId();
        $isCloaked = (bool) $row['is_cloaked'];

        if (!$isOwn) {
            if ($isCloaked) {
                if (!isset($coverage['tachyonKeys'][$key])) {
                    return null;
                }
            } elseif (!isset($coverage['visibleKeys'][$key])) {
                return null;
            }
        }

        return $this->normalizeSpacecraft($row, $user, $canSeeAllianceShips, $relationContext);
    }

    /**
     * @param array<string, mixed> $row
     * @param array{friendlyUserIds: array<int, true>, enemyUserIds: array<int, true>, friendlyAllianceIds: array<int, true>, enemyAllianceIds: array<int, true>} $relationContext
     * @return array<string, mixed>
     */
    private function normalizeSpacecraft(array $row, User $user, bool $canSeeAllianceShips, array $relationContext): array
    {
        $alertState = (int) $row['alert_state'];
        $relationship = $this->getSpacecraftRelationship($row, $user, $canSeeAllianceShips, $relationContext);
        if ((bool) $row['is_cloaked'] && !$relationship['hasDetails']) {
            return $this->normalizeCloakedSignature($row, $relationship);
        }

        $spacecraft = [
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
            'nameText' => $this->parseBbCodeText((string) $row['name']),
            'nameHtml' => $this->parseBbCodeHtml((string) $row['name']),
            'type' => (string) $row['type'],
            'userId' => (int) $row['user_id'],
            'userName' => (string) $row['user_name'],
            'userNameText' => $this->parseBbCodeText((string) $row['user_name']),
            'userNameHtml' => $this->parseBbCodeHtml((string) $row['user_name']),
            'allianceId' => $row['alliance_id'] !== null ? (int) $row['alliance_id'] : null,
            'allianceName' => $row['alliance_name'] !== null ? (string) $row['alliance_name'] : null,
            'allianceNameText' => $row['alliance_name'] !== null ? $this->parseBbCodeText((string) $row['alliance_name']) : null,
            'allianceNameHtml' => $row['alliance_name'] !== null ? $this->parseBbCodeHtml((string) $row['alliance_name']) : null,
            'rumpId' => (int) $row['rump_id'],
            'rumpName' => (string) $row['rump_name'],
            'rumpImage' => $this->getRumpImage((int) $row['rump_id'], (bool) $row['is_cloaked']),
            'x' => (int) $row['x'],
            'y' => (int) $row['y'],
            'inSystem' => (bool) $row['in_system'],
            'systemName' => $row['system_name'] !== null ? (string) $row['system_name'] : null,
            'isCloaked' => (bool) $row['is_cloaked'],
            'isOwn' => $relationship['isOwn'],
            'isFriendly' => $relationship['isFriendly'],
            'isEnemy' => $relationship['isEnemy'],
            'hasDetails' => $relationship['hasDetails'],
            'isSensorContact' => true
        ];

        if (!$relationship['hasDetails']) {
            return $spacecraft;
        }

        return $spacecraft + [
            'hull' => (int) $row['hull'],
            'maxHull' => (int) $row['max_hull'],
            'shield' => (int) $row['shield'],
            'maxShield' => (int) $row['max_shield'],
            'eps' => (int) $row['eps'],
            'maxEps' => (int) $row['max_eps'],
            'warpdrive' => (int) $row['warpdrive'],
            'maxWarpdrive' => (int) $row['max_warpdrive'],
            'alertState' => $alertState,
            'alertStateName' => SpacecraftAlertStateEnum::tryFrom($alertState)?->getDescription() ?? 'Unbekannt'
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @param array{isOwn: bool, isFriendly: bool, isEnemy: bool, hasDetails: bool} $relationship
     * @return array<string, mixed>
     */
    private function normalizeCloakedSignature(array $row, array $relationship): array
    {
        return [
            'id' => (int) $row['id'],
            'name' => '?',
            'nameText' => '?',
            'nameHtml' => '?',
            'type' => (string) $row['type'],
            'userId' => 0,
            'userName' => 'Unbekannt',
            'userNameText' => 'Unbekannt',
            'userNameHtml' => 'Unbekannt',
            'allianceId' => null,
            'allianceName' => null,
            'allianceNameText' => null,
            'allianceNameHtml' => null,
            'rumpId' => 0,
            'rumpName' => 'Tarnsignatur',
            'rumpImage' => '',
            'x' => (int) $row['x'],
            'y' => (int) $row['y'],
            'inSystem' => (bool) $row['in_system'],
            'systemName' => $row['system_name'] !== null ? (string) $row['system_name'] : null,
            'isCloaked' => true,
            'isCloakedSignature' => true,
            'isOwn' => $relationship['isOwn'],
            'isFriendly' => false,
            'isEnemy' => false,
            'hasDetails' => false,
            'isSensorContact' => true
        ];
    }

    /**
     * @param array{friendlyUserIds: array<int, true>, enemyUserIds: array<int, true>, friendlyAllianceIds: array<int, true>, enemyAllianceIds: array<int, true>} $relationContext
     * @return array<string, mixed>
     */
    private function getRealtimeTokenClaims(?int $allianceId, bool $canSeeAllianceShips, array $relationContext): array
    {
        return [
            'allianceId' => $allianceId,
            'canSeeAllianceShips' => $canSeeAllianceShips,
            'friendlyUserIds' => array_keys($relationContext['friendlyUserIds']),
            'enemyUserIds' => array_keys($relationContext['enemyUserIds']),
            'friendlyAllianceIds' => array_keys($relationContext['friendlyAllianceIds']),
            'enemyAllianceIds' => array_keys($relationContext['enemyAllianceIds'])
        ];
    }

    /**
     * @return array{friendlyUserIds: array<int, true>, enemyUserIds: array<int, true>, friendlyAllianceIds: array<int, true>, enemyAllianceIds: array<int, true>}
     */
    private function getRelationContext(User $user): array
    {
        $friendlyUserIds = [];
        $enemyUserIds = [];
        foreach ($this->contactRepository->getOrderedByUser($user) as $contact) {
            $recipientId = $contact->getRecipientId();
            if ($contact->getMode() === ContactListModeEnum::FRIEND) {
                $friendlyUserIds[$recipientId] = true;
            }
            if ($contact->getMode() === ContactListModeEnum::ENEMY) {
                $enemyUserIds[$recipientId] = true;
            }
        }

        $friendlyAllianceIds = [];
        $enemyAllianceIds = [];
        $alliance = $user->getAlliance();
        if ($alliance !== null) {
            $ownAllianceId = $alliance->getId();
            $friendlyAllianceIds[$ownAllianceId] = true;

            foreach ($this->allianceRelationRepository->getActiveByAlliance($ownAllianceId) as $relation) {
                $opponentId = $relation->getAllianceId() === $ownAllianceId
                    ? $relation->getOpponentId()
                    : $relation->getAllianceId();

                if ($relation->getType() === AllianceRelationTypeEnum::WAR) {
                    $enemyAllianceIds[$opponentId] = true;
                }
                if (in_array($relation->getType(), [
                    AllianceRelationTypeEnum::FRIENDS,
                    AllianceRelationTypeEnum::ALLIED,
                    AllianceRelationTypeEnum::VASSAL
                ], true)) {
                    $friendlyAllianceIds[$opponentId] = true;
                }
            }
        }

        return [
            'friendlyUserIds' => $friendlyUserIds,
            'enemyUserIds' => $enemyUserIds,
            'friendlyAllianceIds' => $friendlyAllianceIds,
            'enemyAllianceIds' => $enemyAllianceIds
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @param array{friendlyUserIds: array<int, true>, enemyUserIds: array<int, true>, friendlyAllianceIds: array<int, true>, enemyAllianceIds: array<int, true>} $relationContext
     * @return array{isOwn: bool, isFriendly: bool, isEnemy: bool, hasDetails: bool}
     */
    private function getSpacecraftRelationship(array $row, User $user, bool $canSeeAllianceShips, array $relationContext): array
    {
        $ownerId = (int) $row['user_id'];
        $isOwn = $ownerId === $user->getId();
        $ownAllianceId = $user->getAlliance()?->getId();
        $allianceId = $row['alliance_id'] !== null ? (int) $row['alliance_id'] : null;
        $isOwnAlliance = $ownAllianceId !== null && $allianceId === $ownAllianceId;

        $isFriendly = $isOwn
            || $isOwnAlliance
            || isset($relationContext['friendlyUserIds'][$ownerId])
            || ($allianceId !== null && isset($relationContext['friendlyAllianceIds'][$allianceId]));
        $isEnemy = !$isFriendly
            && (
                isset($relationContext['enemyUserIds'][$ownerId])
                || ($allianceId !== null && isset($relationContext['enemyAllianceIds'][$allianceId]))
            );

        return [
            'isOwn' => $isOwn,
            'isFriendly' => $isFriendly,
            'isEnemy' => $isEnemy,
            'hasDetails' => $isOwn || ($canSeeAllianceShips && $isOwnAlliance)
        ];
    }

    private function parseBbCodeText(string $value): string
    {
        return $this->bbCodeTextCache[$value] ??= trim($this->bbCodeParser->parse($value)->getAsText());
    }

    private function parseBbCodeHtml(string $value): string
    {
        return $this->bbCodeHtmlCache[$value] ??= $this->bbCodeParser->parse($value)->getAsHTML();
    }

    private function getRumpImage(int $rumpId, bool $isCloaked): string
    {
        return sprintf('/assets/ships/%d%s.png', $rumpId, $isCloaked ? '_cloaked' : '');
    }
}
