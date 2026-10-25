<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Lib;

use JBBCode\Parser;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Orm\Entity\User;

/**
 * @phpstan-import-type RelationContext from StarmapRelations
 * @phpstan-import-type SpacecraftRelationship from StarmapRelations
 */
final class StarmapSensorData
{
    /** @var array<string, string> */
    private array $bbCodeTextCache = [];

    /** @var array<string, string> */
    private array $bbCodeHtmlCache = [];

    public function __construct(
        private Parser $bbCodeParser,
        private StarmapRelations $relations
    ) {}

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
        $key = sprintf('%d:%d', (int) $row['x'], (int) $row['y']);
        $relationship = $this->relations->getSpacecraftRelationship($row, $user, $canSeeAllianceShips, $relationContext);
        $isCloaked = (bool) $row['is_cloaked'];

        if (!$relationship['isOwn'] && !$relationship['hasSharedLiveMapPosition']) {
            if ($isCloaked) {
                if (!isset($coverage['tachyonKeys'][$key])) {
                    return null;
                }
            } elseif (!isset($coverage['visibleKeys'][$key])) {
                return null;
            }
        }

        if (!$reveal && !$relationship['hasDetails']) {
            $effects = (string) ($row['field_effects'] ?? '');
            if (str_contains($effects, FieldTypeEffectEnum::NO_SPACECRAFT_COUNT->value)) {
                return null;
            }
            if (str_contains($effects, FieldTypeEffectEnum::DUBIOUS_SPACECRAFT_COUNT->value)) {
                return ['isDubiousSignature' => true, 'x' => (int) $row['x'], 'y' => (int) $row['y']];
            }
        }

        return $this->normalizeSpacecraft($row, $relationship, $reveal);
    }

    /**
     * @param array<string, mixed> $row
     * @param SpacecraftRelationship $relationship
     * @return array<string, mixed>
     */
    private function normalizeSpacecraft(
        array $row,
        array $relationship,
        bool $reveal
    ): array {
        $alertState = (int) $row['alert_state'];
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
