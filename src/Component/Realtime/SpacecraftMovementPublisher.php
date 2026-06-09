<?php

declare(strict_types=1);

namespace Stu\Component\Realtime;

use JBBCode\Parser;
use Stu\Component\Map\DirectionEnum;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\StarSystemMap;
use Throwable;

final class SpacecraftMovementPublisher implements SpacecraftMovementPublisherInterface
{
    private const int JSON_FLAGS = JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE;

    /** @var array<string, string> */
    private array $bbCodeTextCache = [];

    /** @var array<string, string> */
    private array $bbCodeHtmlCache = [];

    public function __construct(
        private RealtimeRedisFactory $redisFactory,
        private Parser $bbCodeParser
    ) {}

    #[\Override]
    public function publishMovement(
        Spacecraft $spacecraft,
        DirectionEnum $direction,
        Location $currentLocation,
        Location $nextLocation
    ): void {
        $currentMap = $this->normalizeMapLocation($currentLocation);
        $nextMap = $this->normalizeMapLocation($nextLocation);

        if ($currentMap === null || $nextMap === null || $currentMap['layerId'] !== $nextMap['layerId']) {
            return;
        }

        try {
            $payload = json_encode([
                'type' => 'spacecraftMovement',
                'generatedAt' => time(),
                'layerId' => $nextMap['layerId'],
                'direction' => $direction->value,
                'from' => [
                    'x' => $currentMap['x'],
                    'y' => $currentMap['y']
                ],
                'to' => [
                    'x' => $nextMap['x'],
                    'y' => $nextMap['y']
                ],
                'spacecraft' => $this->normalizeSpacecraft($spacecraft, $nextMap['x'], $nextMap['y'])
            ], self::JSON_FLAGS);

            $redis = $this->redisFactory->create();
            if ($redis === null) {
                return;
            }

            $redis->xAdd(
                RealtimeChannels::STARMAP_SPACECRAFT_STREAM,
                '*',
                ['payload' => $payload],
                10000,
                true
            );
        } catch (Throwable) {
            return;
        }
    }

    #[\Override]
    public function publishRemoval(Spacecraft $spacecraft): void
    {
        $map = $this->normalizeMapLocation($spacecraft->getLocation());
        if ($map === null) {
            return;
        }

        try {
            $user = $spacecraft->getUser();
            $alliance = $user->getAlliance();
            $payload = json_encode([
                'type' => 'spacecraftRemoval',
                'generatedAt' => time(),
                'layerId' => $map['layerId'],
                'position' => [
                    'x' => $map['x'],
                    'y' => $map['y']
                ],
                'spacecraft' => [
                    'id' => $spacecraft->getId(),
                    'userId' => $user->getId(),
                    'allianceId' => $alliance?->getId(),
                    'isCloaked' => $spacecraft->isCloaked()
                ]
            ], self::JSON_FLAGS);

            $redis = $this->redisFactory->create();
            if ($redis === null) {
                return;
            }

            $redis->xAdd(
                RealtimeChannels::STARMAP_SPACECRAFT_STREAM,
                '*',
                ['payload' => $payload],
                10000,
                true
            );
        } catch (Throwable) {
            return;
        }
    }

    #[\Override]
    public function publishState(Spacecraft $spacecraft): void
    {
        $map = $this->normalizeMapLocation($spacecraft->getLocation());
        if ($map === null) {
            return;
        }

        try {
            $payload = json_encode([
                'type' => 'spacecraftState',
                'generatedAt' => time(),
                'layerId' => $map['layerId'],
                'position' => [
                    'x' => $map['x'],
                    'y' => $map['y']
                ],
                'spacecraft' => $this->normalizeSpacecraft($spacecraft, $map['x'], $map['y'])
            ], self::JSON_FLAGS);

            $redis = $this->redisFactory->create();
            if ($redis === null) {
                return;
            }

            $redis->xAdd(
                RealtimeChannels::STARMAP_SPACECRAFT_STREAM,
                '*',
                ['payload' => $payload],
                10000,
                true
            );
        } catch (Throwable) {
            return;
        }
    }

    /**
     * @return null|array{layerId: int, x: int, y: int}
     */
    private function normalizeMapLocation(Location $location): ?array
    {
        $map = $location instanceof Map ? $location : ($location instanceof StarSystemMap ? $location->getSystem()->getMap() : null);
        if (!$map instanceof Map) {
            return null;
        }
        $layer = $map->getLayer();
        if ($layer === null) {
            return null;
        }

        return [
            'layerId' => $layer->getId(),
            'x' => $map->getX(),
            'y' => $map->getY()
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function normalizeSpacecraft(Spacecraft $spacecraft, int $x, int $y): array
    {
        $user = $spacecraft->getUser();
        $alliance = $user->getAlliance();
        $alertState = $this->getAlertState($spacecraft);

        return [
            'id' => $spacecraft->getId(),
            'name' => $spacecraft->getName(),
            'nameText' => $this->parseBbCodeText($spacecraft->getName()),
            'nameHtml' => $this->parseBbCodeHtml($spacecraft->getName()),
            'type' => $spacecraft->getType()->value,
            'userId' => $user->getId(),
            'userName' => $user->getName(),
            'userNameText' => $this->parseBbCodeText($user->getName()),
            'userNameHtml' => $this->parseBbCodeHtml($user->getName()),
            'allianceId' => $alliance?->getId(),
            'allianceName' => $alliance?->getName(),
            'allianceNameText' => $alliance !== null ? $this->parseBbCodeText($alliance->getName()) : null,
            'allianceNameHtml' => $alliance !== null ? $this->parseBbCodeHtml($alliance->getName()) : null,
            'rumpId' => $spacecraft->getRump()->getId(),
            'rumpName' => $spacecraft->getRump()->getName(),
            'rumpImage' => sprintf('/assets/ships/%d%s.png', $spacecraft->getRump()->getId(), $spacecraft->isCloaked() ? '_cloaked' : ''),
            'x' => $x,
            'y' => $y,
            'inSystem' => $spacecraft->getStarsystemMap() !== null,
            'systemName' => $spacecraft->getStarsystemMap()?->getSystem()->getName(),
            'isCloaked' => $spacecraft->isCloaked(),
            'hull' => $spacecraft->getCondition()->getHull(),
            'maxHull' => $spacecraft->getMaxHull(),
            'shield' => $spacecraft->getCondition()->getShield(),
            'maxShield' => $spacecraft->getMaxShield(),
            'eps' => $this->getSystemDataInt($spacecraft, SpacecraftSystemTypeEnum::EPS, 'eps'),
            'maxEps' => $this->getSystemDataMaxByStatus($spacecraft, SpacecraftSystemTypeEnum::EPS, 'maxEps'),
            'warpdrive' => $this->getSystemDataInt($spacecraft, SpacecraftSystemTypeEnum::WARPDRIVE, 'wd'),
            'maxWarpdrive' => $this->getSystemDataMaxByStatus($spacecraft, SpacecraftSystemTypeEnum::WARPDRIVE, 'maxwd'),
            'alertState' => $alertState,
            'alertStateName' => SpacecraftAlertStateEnum::tryFrom($alertState)?->getDescription() ?? 'Unbekannt'
        ];
    }

    private function getSystemDataInt(Spacecraft $spacecraft, SpacecraftSystemTypeEnum $systemType, string $key): int
    {
        $system = $spacecraft->getSystems()->get($systemType->value);
        if ($system === null) {
            return 0;
        }

        $data = json_decode((string) $system->getData(), true);

        return is_array($data) && isset($data[$key]) ? (int) $data[$key] : 0;
    }

    private function getSystemDataMaxByStatus(Spacecraft $spacecraft, SpacecraftSystemTypeEnum $systemType, string $key): int
    {
        $system = $spacecraft->getSystems()->get($systemType->value);
        if ($system === null) {
            return 0;
        }

        $data = json_decode((string) $system->getData(), true);
        $value = is_array($data) && isset($data[$key]) ? (int) $data[$key] : 0;

        return (int) ceil($value * $system->getStatus() / 100);
    }

    private function getAlertState(Spacecraft $spacecraft): int
    {
        return $this->getSystemDataInt($spacecraft, SpacecraftSystemTypeEnum::COMPUTER, 'alertState') ?: 1;
    }

    private function parseBbCodeText(string $value): string
    {
        return $this->bbCodeTextCache[$value] ??= trim($this->bbCodeParser->parse($value)->getAsText());
    }

    private function parseBbCodeHtml(string $value): string
    {
        return $this->bbCodeHtmlCache[$value] ??= $this->bbCodeParser->parse($value)->getAsHTML();
    }
}
