<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowUserStarmapData;

use JBBCode\Parser;
use JsonException;
use request;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Lib\Trait\LayerExplorationTrait;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Starmap\Lib\ExploreableStarMapInterface;
use Stu\Module\Starmap\Lib\StarmapUiFactoryInterface;
use Stu\Module\Starmap\View\ShowUserStarmapImage\ShowUserStarmapImage;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

final class ShowUserStarmapData implements ViewControllerInterface
{
    use LayerExplorationTrait;

    public const string VIEW_IDENTIFIER = 'SHOW_USER_STARMAP_DATA';

    private const int JSON_FLAGS = JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE;

    /** @var array<string, string> */
    private array $bbCodeTextCache = [];

    /** @var array<string, string> */
    private array $bbCodeHtmlCache = [];

    public function __construct(
        private LayerRepositoryInterface $layerRepository,
        private MapRepositoryInterface $mapRepository,
        private UserMapRepositoryInterface $userMapRepository,
        private StarmapUiFactoryInterface $starmapUiFactory,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private AllianceJobManagerInterface $allianceJobManager,
        private AllianceRelationRepositoryInterface $allianceRelationRepository,
        private ContactRepositoryInterface $contactRepository,
        private Parser $bbCodeParser
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
        $alliance = $user->getAlliance();
        $canSeeAllianceShips = $alliance !== null
            && $this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::VIEW_SHIPS);
        $spacecrafts = $this->spacecraftRepository->getUserStarmapSpacecrafts(
            $user->getId(),
            $layer->getId(),
            $alliance?->getId(),
            $canSeeAllianceShips,
            $visibility['full']
        );
        $relationContext = $this->getRelationContext($user);

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        echo json_encode([
            'generatedAt' => time(),
            'cellSize' => ShowUserStarmapImage::CELL_SIZE,
            'imageVersion' => $visibility['version'],
            'fullyExplored' => $visibility['full'],
            'visibleRuns' => $visibility['runs'],
            'visibleFieldCount' => count($fields),
            'canSeeAllianceShips' => $canSeeAllianceShips,
            'layer' => [
                'id' => $layer->getId(),
                'name' => $layer->getName(),
                'width' => $layer->getWidth(),
                'height' => $layer->getHeight()
            ],
            'fields' => array_map(
                fn (ExploreableStarMapInterface $field): array => $this->normalizeField($field, $layer),
                $fields
            ),
            'spacecrafts' => array_map(
                fn (array $row): array => $this->normalizeSpacecraft($row, $user, $canSeeAllianceShips, $relationContext),
                $spacecrafts
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
        $territoryOwner = $this->getTerritoryOwner($field);

        return [
            'x' => $item->getCx(),
            'y' => $item->getCy(),
            'tooltip' => $this->removeTooltipLine($item->getTooltip(), $territoryOwner['text'] ?? null),
            'icon' => $icon !== null ? sprintf('/assets/map/%s.png', $icon) : null,
            'databaseId' => $field->getMapped(),
            'hasTerritory' => $item->hasTerritory(),
            'territoryColor' => $this->extractColor($item->getTerritoryStyle()),
            'territoryOwnerText' => $territoryOwner['text'] ?? null,
            'territoryOwnerHtml' => $territoryOwner['html'] ?? null,
            'hasEffects' => $item->hasEffects(),
            'effects' => array_map(
                static fn (FieldTypeEffectEnum $effect): string => $effect->value,
                $field->getEffects()
            ),
            'isImpassable' => $item->isImpassable()
        ];
    }

    /**
     * @return null|array{text: string, html: string}
     */
    private function getTerritoryOwner(ExploreableStarMapInterface $field): ?array
    {
        if ($field->getAdminRegion() !== null) {
            return null;
        }

        $influenceArea = $field->getInfluenceArea();
        if ($influenceArea === null) {
            return null;
        }

        $base = $influenceArea->getStation();
        if ($base === null) {
            return null;
        }

        $user = $base->getUser();
        $userNameText = trim($this->parseBbCodeText($user->getName()));
        if ($userNameText === '') {
            return null;
        }

        $userNameHtml = $this->parseBbCodeHtml($user->getName());
        $alliance = $user->getAlliance();
        if ($alliance !== null) {
            $allianceNameText = trim($this->parseBbCodeText($alliance->getName()));
            if ($allianceNameText !== '') {
                return [
                    'text' => sprintf('Gebiet: %s (%s)', $allianceNameText, $userNameText),
                    'html' => sprintf(
                        'Gebiet: %s (%s)',
                        $this->parseBbCodeHtml($alliance->getName()),
                        $userNameHtml
                    )
                ];
            }
        }

        return [
            'text' => sprintf('Gebiet: %s', $userNameText),
            'html' => sprintf('Gebiet: %s', $userNameHtml)
        ];
    }

    private function removeTooltipLine(string $tooltip, ?string $lineToRemove): string
    {
        if ($lineToRemove === null || $lineToRemove === '') {
            return $tooltip;
        }

        $lineToRemove = trim($lineToRemove);

        return implode("\n", array_filter(
            explode("\n", $tooltip),
            fn (string $line): bool => trim($line) !== $lineToRemove
        ));
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
            'hasDetails' => $relationship['hasDetails']
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
            'alertStateName' => $this->getAlertStateName($alertState)
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
            $allianceId = $alliance->getId();
            $friendlyAllianceIds[$allianceId] = true;

            foreach ($this->allianceRelationRepository->getActiveByAlliance($allianceId) as $relation) {
                $opponentId = $relation->getAllianceId() === $allianceId
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

    private function getAlertStateName(int $alertState): string
    {
        return SpacecraftAlertStateEnum::tryFrom($alertState)?->getDescription() ?? 'Unbekannt';
    }

    private function extractColor(string $style): ?string
    {
        if (preg_match('/#[0-9a-fA-F]{6}/', $style, $matches) !== 1) {
            return null;
        }

        return $matches[0];
    }
}
