<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map\LiveMap;

use JBBCode\Parser;
use JsonException;
use request;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class ShowLiveMapData implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ADMIN_LIVE_MAP_DATA';

    private const int JSON_FLAGS = JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE;
    private const int DEFAULT_SIGNATURE_LIMIT = 10000;
    private const int MAX_SIGNATURE_LIMIT = 250000;
    private const int SELECTED_SHIP_SIGNATURE_LIMIT = 800;

    /** @var array<string, string> */
    private array $bbCodeTextCache = [];

    /** @var array<string, string> */
    private array $bbCodeHtmlCache = [];

    public function __construct(
        private LayerRepositoryInterface $layerRepository,
        private MapRepositoryInterface $mapRepository,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private FlightSignatureRepositoryInterface $flightSignatureRepository,
        private Parser $bbCodeParser
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

        $now = time();
        $signatureLimit = min(
            self::MAX_SIGNATURE_LIMIT,
            max(1, request::getInt('signatureLimit', self::DEFAULT_SIGNATURE_LIMIT))
        );
        $maxSignatureAge = min(
            FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED,
            max(60, request::getInt('maxSignatureAge', FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED))
        );
        $minSignatureTime = $now - $maxSignatureAge;
        $selectedShipId = request::getInt('shipId');

        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');

        echo '{';
        echo '"generatedAt":' . $now . ',';
        echo '"cellSize":' . ShowLiveMapImage::CELL_SIZE . ',';
        echo '"layer":' . $this->encodeJson([
            'id' => $layer->getId(),
            'name' => $layer->getName(),
            'width' => $layer->getWidth(),
            'height' => $layer->getHeight()
        ]) . ',';
        echo '"spacecrafts":' . $this->encodeJson(array_map(
            fn (array $row): array => $this->normalizeSpacecraft($row),
            $this->spacecraftRepository->getAdminLiveMapSpacecrafts($layer->getId())
        )) . ',';
        echo '"flightSignatures":';
        $this->writeFlightSignatures($this->flightSignatureRepository->iterateAdminLiveMapFlightSignatures(
            $layer->getId(),
            $minSignatureTime,
            $signatureLimit
        ), $now);
        echo ',"signatureDetailLimit":' . $signatureLimit . ',';
        echo '"maxSignatureAge":' . $maxSignatureAge . ',';
        echo '"overlays":' . $this->encodeJson($this->normalizeOverlays(
            $this->mapRepository->getAdminLiveMapOverlayFields($layer->getId())
        ));

        if ($selectedShipId > 0) {
            echo ',"selectedShipSignatures":';
            $this->writeFlightSignatures($this->flightSignatureRepository->iterateAdminLiveMapFlightSignaturesForShip(
                $layer->getId(),
                $minSignatureTime,
                $selectedShipId,
                self::SELECTED_SHIP_SIGNATURE_LIMIT
            ), $now);
        }

        echo '}';
        exit;
    }

    /**
     * @param iterable<array<string, mixed>> $rows
     * @throws JsonException
     */
    private function writeFlightSignatures(iterable $rows, int $now): void
    {
        echo '[';
        $first = true;
        $count = 0;
        foreach ($rows as $row) {
            if (!$first) {
                echo ',';
            }
            $first = false;
            echo $this->encodeJson($this->normalizeFlightSignature($row, $now));
            $count++;
            if ($count % 1000 === 0) {
                $this->flushOutput();
            }
        }
        echo ']';
    }

    private function flushOutput(): void
    {
        if (ob_get_level() > 0) {
            @ob_flush();
        }
        flush();
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalizeSpacecraft(array $row): array
    {
        return [
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
            'isCloaked' => (bool) $row['is_cloaked']
        ];
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalizeFlightSignature(array $row, int $now): array
    {
        return [
            'id' => (int) $row['id'],
            'shipId' => (int) $row['ship_id'],
            'shipName' => (string) $row['ship_name'],
            'shipNameText' => $this->parseBbCodeText((string) $row['ship_name']),
            'shipNameHtml' => $this->parseBbCodeHtml((string) $row['ship_name']),
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
            'time' => (int) $row['time'],
            'age' => $now - (int) $row['time'],
            'fromDirection' => $row['from_direction'] !== null ? (int) $row['from_direction'] : null,
            'toDirection' => $row['to_direction'] !== null ? (int) $row['to_direction'] : null,
            'inSystem' => (bool) $row['in_system'],
            'systemName' => $row['system_name'] !== null ? (string) $row['system_name'] : null,
            'isCloaked' => (bool) $row['is_cloaked']
        ];
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     * @return array{territory: array<int, array<string, mixed>>, impassable: array<int, array<string, mixed>>}
     */
    private function normalizeOverlays(array $rows): array
    {
        $territory = [];
        $impassable = [];

        foreach ($rows as $row) {
            $x = (int) $row['x'];
            $y = (int) $row['y'];
            $territoryColor = $this->normalizeColor($row['territory_color'] !== null ? (string) $row['territory_color'] : null);

            if ($territoryColor !== null) {
                $territory[] = [
                    'x' => $x,
                    'y' => $y,
                    'color' => $territoryColor,
                    'name' => $row['territory_name'] !== null ? $this->parseBbCodeText((string) $row['territory_name']) : null
                ];
            }

            if ((bool) $row['impassable']) {
                $impassableColor = $this->normalizeColor(
                    $row['impassable_color'] !== null ? (string) $row['impassable_color'] : null
                );
                $impassable[] = [
                    'x' => $x,
                    'y' => $y,
                    'color' => $impassableColor ?? '#730505'
                ];
            }
        }

        return [
            'territory' => $territory,
            'impassable' => $impassable
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

    /**
     * @throws JsonException
     */
    private function encodeJson(mixed $value): string
    {
        return json_encode($value, self::JSON_FLAGS);
    }

    private function getRumpImage(int $rumpId, bool $isCloaked): string
    {
        return sprintf('/assets/ships/%d%s.png', $rumpId, $isCloaked ? '_cloaked' : '');
    }

    private function normalizeColor(?string $color): ?string
    {
        if ($color === null || $color === '') {
            return null;
        }

        $trimmed = trim($color);
        if (preg_match('/^#?[0-9a-fA-F]{6}$/', $trimmed) !== 1) {
            return null;
        }

        return $trimmed[0] === '#' ? $trimmed : '#' . $trimmed;
    }
}
