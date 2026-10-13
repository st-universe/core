<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use JBBCode\Parser;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\Alliance\Enum\RelationPermissionEnum;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Lib\Map\VisualPanel\LssBlockade\LssBlockadeGrid;
use Stu\Lib\Map\VisualPanel\PanelBoundaries;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\RelationRepositoryInterface;

/**
 * @phpstan-type RelationContext array{
 *     friendlyUserIds: array<int, true>,
 *     enemyUserIds: array<int, true>,
 *     friendlyAllianceIds: array<int, true>,
 *     enemyAllianceIds: array<int, true>,
 *     sharedUserIds: array<int, true>,
 *     sharedAllianceIds: array<int, true>
 * }
 * @phpstan-type SpacecraftRelationship array{
 *     isOwn: bool,
 *     isFriendly: bool,
 *     isEnemy: bool,
 *     hasDetails: bool,
 *     hasSharedLiveMapPosition: bool
 * }
 */
final class StarmapSensorData
{
    /** @var array<string, string> */
    private array $bbCodeTextCache = [];

    /** @var array<string, string> */
    private array $bbCodeHtmlCache = [];

    public function __construct(
        private Parser $bbCodeParser,
        private RelationRepositoryInterface $allianceRelationRepository,
        private ContactRepositoryInterface $contactRepository,
        private MapRepositoryInterface $mapRepository
    ) {}

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
    public function buildSensorCoverage(Layer $layer, array $ranges): array
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
            $tachyonRange = $this->getEffectiveTachyonRange($sensorRange, max(
                0,
                (int) ($range['tachyon_range'] ?? 0)
            ));
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
            $effects = $entry['effects'];
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
                if ($x === ($lastX + 1)) {
                    $lastX = $x;
                    continue;
                }
                $runs[] = ['y' => $y, 'startX' => $startX, 'endX' => $lastX];
                $startX = $lastX = $x;
            }
            $runs[] = ['y' => $y, 'startX' => $startX, 'endX' => $lastX];
        }

        return $runs;
    }

    private function fieldKey(int $x, int $y): string
    {
        return sprintf('%d:%d', $x, $y);
    }

    /**
     * @param array<string, mixed> $row
     * @param RelationContext $relationContext
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
    public function normalizeVisibleSpacecraft(
        array $row,
        User $user,
        bool $canSeeAllianceShips,
        array $relationContext,
        array $coverage,
        bool $reveal = false
    ): ?array {
        $key = $this->fieldKey((int) $row['x'], (int) $row['y']);
        $isOwn = (int) $row['user_id'] === $user->getId();
        $allianceId = $row['alliance_id'] !== null ? (int) $row['alliance_id'] : null;
        $isShared =
            isset($relationContext['sharedUserIds'][(int) $row['user_id']])
            || $allianceId !== null && isset($relationContext['sharedAllianceIds'][$allianceId]);
        $isCloaked = (bool) $row['is_cloaked'];

        if (!$isOwn && !$isShared) {
            if ($isCloaked) {
                if (!isset($coverage['tachyonKeys'][$key])) {
                    return null;
                }
            } elseif (!isset($coverage['visibleKeys'][$key])) {
                return null;
            }
        }

        return $this->normalizeSpacecraft($row, $user, $canSeeAllianceShips, $relationContext, $reveal);
    }

    /**
     * @param array<string, mixed> $row
     * @param RelationContext $relationContext
     * @return array<string, mixed>
     */
    private function normalizeSpacecraft(
        array $row,
        User $user,
        bool $canSeeAllianceShips,
        array $relationContext,
        bool $reveal
    ): array {
        $alertState = (int) $row['alert_state'];
        $relationship = $this->getSpacecraftRelationship($row, $user, $canSeeAllianceShips, $relationContext);
        if (
            (bool) $row['is_cloaked']
            && !$relationship['hasDetails']
            && !$relationship['hasSharedLiveMapPosition']
        ) {
            return $this->normalizeSignature($row, $relationship);
        }

        if (!$reveal && !$relationship['isOwn'] && !$relationship['isFriendly']) {
            return $this->normalizeSignature($row, $relationship);
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
            'allianceNameText' => $row['alliance_name'] !== null
                ? $this->parseBbCodeText((string) $row['alliance_name'])
                : null,
            'allianceNameHtml' => $row['alliance_name'] !== null
                ? $this->parseBbCodeHtml((string) $row['alliance_name'])
                : null,
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
            'isSensorContact' => !$relationship['hasDetails'] && !$relationship['hasSharedLiveMapPosition']
        ];

        if (!$relationship['hasDetails']) {
            return $spacecraft;
        }

        return $spacecraft
        + [
            'hull' => (int) $row['hull'],
            'maxHull' => (int) $row['max_hull'],
            'shield' => (int) $row['shield'],
            'maxShield' => (int) $row['max_shield'],
            'eps' => (int) $row['eps'],
            'maxEps' => (int) $row['max_eps'],
            'warpdrive' => (int) $row['warpdrive'],
            'maxWarpdrive' => (int) $row['max_warpdrive'],
            'alertState' => $alertState,
            'alertStateName' =>
                SpacecraftAlertStateEnum::tryFrom($alertState)?->getDescription() ?? 'Unbekannt'
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @param SpacecraftRelationship $relationship
     * @return array<string, mixed>
     */
    private function normalizeSignature(array $row, array $relationship): array
    {
        $isCloaked = (bool) $row['is_cloaked'];

        return [
            'id' => (int) $row['id'],
            'name' => $isCloaked ? '?' : 'Unbekannte Signatur',
            'nameText' => $isCloaked ? '?' : 'Unbekannte Signatur',
            'nameHtml' => $isCloaked ? '?' : 'Unbekannte Signatur',
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
            'rumpName' => $isCloaked ? 'Tarnsignatur' : 'Sensorsignatur',
            'rumpImage' => '',
            'x' => (int) $row['x'],
            'y' => (int) $row['y'],
            'inSystem' => (bool) $row['in_system'],
            'systemName' => $row['system_name'] !== null ? (string) $row['system_name'] : null,
            'isCloaked' => $isCloaked,
            'isCloakedSignature' => $isCloaked,
            'isUnscannedSignature' => !$isCloaked,
            'isOwn' => $relationship['isOwn'],
            'isFriendly' => false,
            'isEnemy' => false,
            'hasDetails' => false,
            'isSensorContact' => true
        ];
    }

    /**
     * @param RelationContext $relationContext
     * @return array<string, mixed>
     */
    public function getRealtimeTokenClaims(
        ?int $allianceId,
        bool $canSeeAllianceShips,
        array $relationContext
    ): array {
        return [
            'allianceId' => $allianceId,
            'canSeeAllianceShips' => $canSeeAllianceShips,
            'friendlyUserIds' => array_keys($relationContext['friendlyUserIds']),
            'enemyUserIds' => array_keys($relationContext['enemyUserIds']),
            'friendlyAllianceIds' => array_keys($relationContext['friendlyAllianceIds']),
            'enemyAllianceIds' => array_keys($relationContext['enemyAllianceIds']),
            'sharedUserIds' => array_keys($relationContext['sharedUserIds']),
            'sharedAllianceIds' => array_keys($relationContext['sharedAllianceIds'])
        ];
    }

    /**
     * @return RelationContext
     */
    public function getRelationContext(User $user): array
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

        $sharedUserIds = [];
        foreach ($this->contactRepository->getByRecipient($user) as $contact) {
            if ($contact->hasPermission(RelationPermissionEnum::SHARE_LIVE_MAP_POSITIONS)) {
                $sharedUserIds[$contact->getUserId()] = true;
            }
        }

        $friendlyAllianceIds = [];
        $enemyAllianceIds = [];
        $sharedAllianceIds = [];
        $alliance = $user->getAlliance();
        foreach ($this->allianceRelationRepository->getByUserAndAlliance($user, $alliance) as $relation) {
            if ($relation->isPending()) {
                continue;
            }

            $sourceIsUser = $relation->getSourceUser()?->getId() === $user->getId();
            $sourceIsAlliance =
                $alliance !== null && $relation->getSourceAlliance()?->getId() === $alliance->getId();
            $recipientIsUser = $relation->getRecipientUser()?->getId() === $user->getId();
            $recipientIsAlliance =
                $alliance !== null && $relation->getRecipientAlliance()?->getId() === $alliance->getId();
            $isSource = $sourceIsUser || $sourceIsAlliance;
            if (!$isSource && !$recipientIsUser && !$recipientIsAlliance) {
                continue;
            }

            $counterpart = $isSource ? $relation->getRecipientParty() : $relation->getSourceParty();
            if ($counterpart instanceof User) {
                if ($relation->getType() === AllianceRelationTypeEnum::WAR) {
                    $enemyUserIds[$counterpart->getId()] = true;
                }
                if ($relation->hasPermissionFor($user, RelationPermissionEnum::FRIENDLY)) {
                    $friendlyUserIds[$counterpart->getId()] = true;
                }
                if ($relation->hasPermissionFor($user, RelationPermissionEnum::SHARE_LIVE_MAP_POSITIONS)) {
                    $sharedUserIds[$counterpart->getId()] = true;
                }
                continue;
            }

            if ($relation->getType() === AllianceRelationTypeEnum::WAR) {
                $enemyAllianceIds[$counterpart->getId()] = true;
            }
            if ($relation->hasPermissionFor($user, RelationPermissionEnum::FRIENDLY)) {
                $friendlyAllianceIds[$counterpart->getId()] = true;
            }
            if ($relation->hasPermissionFor($user, RelationPermissionEnum::SHARE_LIVE_MAP_POSITIONS)) {
                $sharedAllianceIds[$counterpart->getId()] = true;
            }
        }
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
                if ($relation->hasPermissionFor($user, RelationPermissionEnum::FRIENDLY)) {
                    $friendlyAllianceIds[$opponentId] = true;
                }
                if ($relation->hasPermissionFor($user, RelationPermissionEnum::SHARE_LIVE_MAP_POSITIONS)) {
                    $sharedAllianceIds[$opponentId] = true;
                }
            }
        }

        return [
            'friendlyUserIds' => $friendlyUserIds,
            'enemyUserIds' => $enemyUserIds,
            'friendlyAllianceIds' => $friendlyAllianceIds,
            'enemyAllianceIds' => $enemyAllianceIds,
            'sharedUserIds' => $sharedUserIds,
            'sharedAllianceIds' => $sharedAllianceIds
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @param RelationContext $relationContext
     * @return SpacecraftRelationship
     */
    private function getSpacecraftRelationship(
        array $row,
        User $user,
        bool $canSeeAllianceShips,
        array $relationContext
    ): array {
        $ownerId = (int) $row['user_id'];
        $isOwn = $ownerId === $user->getId();
        $ownAllianceId = $user->getAlliance()?->getId();
        $allianceId = $row['alliance_id'] !== null ? (int) $row['alliance_id'] : null;
        $isOwnAlliance = $ownAllianceId !== null && $allianceId === $ownAllianceId;
        $hasSharedLiveMapPosition =
            isset($relationContext['sharedUserIds'][$ownerId])
            || $allianceId !== null && isset($relationContext['sharedAllianceIds'][$allianceId]);

        $isFriendly =
            $isOwn
            || $isOwnAlliance
            || isset($relationContext['friendlyUserIds'][$ownerId])
            || $allianceId !== null && isset($relationContext['friendlyAllianceIds'][$allianceId]);
        $isEnemy =
            !$isFriendly
            && (
                isset($relationContext['enemyUserIds'][$ownerId])
                || $allianceId !== null
                && isset($relationContext['enemyAllianceIds'][$allianceId])
            );

        return [
            'isOwn' => $isOwn,
            'isFriendly' => $isFriendly,
            'isEnemy' => $isEnemy,
            'hasDetails' => $isOwn || $canSeeAllianceShips && $isOwnAlliance,
            'hasSharedLiveMapPosition' => $hasSharedLiveMapPosition
        ];
    }

    public function parseBbCodeText(string $value): string
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
