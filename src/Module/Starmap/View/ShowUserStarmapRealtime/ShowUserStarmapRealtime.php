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
        private ContactRepositoryInterface $contactRepository
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
        $this->cacheCoverage($user->getId(), $layer->getId(), $ranges);

        $spacecrafts = $ranges === []
            ? []
            : $this->spacecraftRepository->getUserStarmapRealtimeSpacecrafts($user->getId(), $layer->getId());
        $relationContext = $this->getRelationContext($user);

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
            'spacecrafts' => array_map(
                fn (array $row): array => $this->normalizeSpacecraft($row, $user, $canSeeAllianceShips, $relationContext),
                $spacecrafts
            )
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
     * @param array<int, array<string, mixed>> $ranges
     */
    private function cacheCoverage(int $userId, int $layerId, array $ranges): void
    {
        try {
            $redis = $this->redisFactory->create();
            if ($redis === null) {
                return;
            }

            $redis->setex(
                RealtimeChannels::starmapCoverageKey($userId, $layerId),
                self::COVERAGE_TTL_SECONDS,
                json_encode(array_map(
                    fn (array $range): array => [
                        'x' => (int) $range['x'],
                        'y' => (int) $range['y'],
                        'range' => (int) $range['sensor_range']
                    ],
                    $ranges
                ), self::JSON_FLAGS)
            );
        } catch (Throwable) {
            return;
        }
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
