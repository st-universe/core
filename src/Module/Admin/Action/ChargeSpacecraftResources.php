<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use request;
use Stu\Component\Spacecraft\System\Data\EpsSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\ReactorWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\StationRepositoryInterface;

final class ChargeSpacecraftResources implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CHARGE_SPACECRAFT';

    private const string TARGET_EPS = 'eps';
    private const string TARGET_WARPDRIVE = 'warpdrive';
    private const string TARGET_SHIELDS = 'shields';
    private const string TARGET_WARPCORE = 'warpcore';
    private const string TARGET_BATTERY = 'battery';

    /** @var array<string, string> */
    private const array TARGET_LABELS = [
        self::TARGET_EPS => 'EPS',
        self::TARGET_WARPDRIVE => 'Warpantrieb',
        self::TARGET_SHIELDS => 'Schilde',
        self::TARGET_WARPCORE => 'Warpkern/Reaktor',
        self::TARGET_BATTERY => 'Ersatzbatterie'
    ];

    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private ShipRepositoryInterface $shipRepository,
        private StationRepositoryInterface $stationRepository,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowScripts::VIEW_IDENTIFIER);

        if (!$game->isAdmin()) {
            $game->getInfo()->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $target = (string) request::postString('spacecraft_charge_target');
        if (!array_key_exists($target, self::TARGET_LABELS)) {
            $game->getInfo()->addInformation(_('Ungültige Ladeoption'));
            return;
        }

        $chargeValue = $this->parseChargeValue((string) request::postString('spacecraft_charge_value'), $game);
        if ($chargeValue === null) {
            return;
        }

        $spacecraftIds = $this->parseIdList((string) request::postString('spacecraft_charge_spacecraft_ids'), 'Spacecraft-ID', $game);
        $userIds = $this->parseIdList((string) request::postString('spacecraft_charge_user_ids'), 'User-ID', $game);
        if ($spacecraftIds === null || $userIds === null) {
            return;
        }

        $includeShips = request::postString('spacecraft_charge_ships') !== false;
        $includeStations = request::postString('spacecraft_charge_stations') !== false;

        $spacecrafts = $this->loadSpacecrafts(
            $spacecraftIds,
            $userIds,
            $includeShips,
            $includeStations,
            request::postString('spacecraft_charge_confirmed') === '1',
            $game
        );
        if ($spacecrafts === null || $spacecrafts === []) {
            return;
        }

        $override = request::postString('spacecraft_charge_override') !== false;
        $updated = 0;

        foreach ($spacecrafts as $spacecraft) {
            if ($this->chargeSpacecraft($spacecraft, $target, $chargeValue['isMax'], $chargeValue['value'], $override)) {
                $updated++;
            }
        }

        $skipped = count($spacecrafts) - $updated;
        $message = sprintf(
            '%d Spacecraft%s %s für %s mit Wert %s verarbeitet%s',
            $updated,
            $updated === 1 ? '' : 's',
            $updated === 1 ? 'wurde' : 'wurden',
            self::TARGET_LABELS[$target],
            $chargeValue['isMax'] ? 'max' : (string) $chargeValue['value'],
            !$chargeValue['isMax'] && $override
                ? ' (Override)'
                : (!$chargeValue['isMax'] ? ' (je Spacecraft auf das aktuelle Maximum begrenzt)' : '')
        );

        if ($skipped > 0) {
            $message .= sprintf(
                '. %d Spacecraft%s %s übersprungen, weil das passende System fehlt',
                $skipped,
                $skipped === 1 ? '' : 's',
                $skipped === 1 ? 'wurde' : 'wurden'
            );
        }

        $game->getInfo()->addInformation($message);
    }

    /**
     * @return null|array{isMax: bool, value: int|null}
     */
    private function parseChargeValue(string $input, GameControllerInterface $game): ?array
    {
        $value = trim($input);

        if ($value === '') {
            $game->getInfo()->addInformation(_('Bitte einen Wert oder max angeben'));
            return null;
        }

        if (strtolower($value) === 'max') {
            return ['isMax' => true, 'value' => null];
        }

        if (!ctype_digit($value) || (int) $value <= 0) {
            $game->getInfo()->addInformation(_('Der Wert muss eine positive Zahl oder max sein'));
            return null;
        }

        return ['isMax' => false, 'value' => (int) $value];
    }

    /**
     * @return null|array<int, int>
     */
    private function parseIdList(string $input, string $label, GameControllerInterface $game): ?array
    {
        $input = trim($input);
        if ($input === '') {
            return [];
        }

        $ids = [];
        foreach (explode(',', $input) as $rawId) {
            $id = trim($rawId);
            if ($id === '' || !ctype_digit($id) || (int) $id <= 0) {
                $game->getInfo()->addInformation(sprintf('%s-Liste enthält einen ungültigen Wert', $label));
                return null;
            }

            $ids[(int) $id] = (int) $id;
        }

        return array_values($ids);
    }

    /**
     * @param array<int, int> $spacecraftIds
     * @param array<int, int> $userIds
     *
     * @return null|array<Spacecraft>
     */
    private function loadSpacecrafts(
        array $spacecraftIds,
        array $userIds,
        bool $includeShips,
        bool $includeStations,
        bool $confirmed,
        GameControllerInterface $game
    ): ?array {
        if ($spacecraftIds !== []) {
            return $this->loadBySpacecraftIds($spacecraftIds, $game);
        }

        if (!$includeShips && !$includeStations) {
            $game->getInfo()->addInformation(_('Bitte Schiffe, Stationen oder konkrete Spacecraft-IDs auswählen'));
            return null;
        }

        if ($userIds !== []) {
            return $this->loadByUserIds($userIds, $includeShips, $includeStations, $game);
        }

        if (!$confirmed) {
            $game->getInfo()->addInformation(_('Bitte die globale Spacecraft-Ladung zuerst bestätigen'));
            return null;
        }

        return $this->loadForAllPlayers($includeShips, $includeStations, $game);
    }

    /**
     * @param array<int, int> $spacecraftIds
     *
     * @return array<Spacecraft>
     */
    private function loadBySpacecraftIds(array $spacecraftIds, GameControllerInterface $game): array
    {
        $spacecrafts = [];
        $missingIds = [];

        foreach ($spacecraftIds as $spacecraftId) {
            $spacecraft = $this->spacecraftRepository->find($spacecraftId);
            if ($spacecraft === null) {
                $missingIds[] = $spacecraftId;
                continue;
            }

            $spacecrafts[$spacecraft->getId()] = $spacecraft;
        }

        if ($missingIds !== []) {
            $game->getInfo()->addInformation(sprintf('Spacecraft-ID%s nicht gefunden: %s', count($missingIds) === 1 ? '' : 's', implode(', ', $missingIds)));
        }

        if ($spacecrafts === []) {
            $game->getInfo()->addInformation(_('Keine Spacecrafts gefunden'));
        }

        return array_values($spacecrafts);
    }

    /**
     * @param array<int, int> $userIds
     *
     * @return array<Spacecraft>
     */
    private function loadByUserIds(array $userIds, bool $includeShips, bool $includeStations, GameControllerInterface $game): array
    {
        $spacecrafts = [];

        if ($includeShips) {
            foreach ($this->shipRepository->getByUserIds($userIds) as $ship) {
                $spacecrafts[$ship->getId()] = $ship;
            }
        }

        if ($includeStations) {
            foreach ($this->stationRepository->getByUserIds($userIds) as $station) {
                $spacecrafts[$station->getId()] = $station;
            }
        }

        if ($spacecrafts === []) {
            $game->getInfo()->addInformation(_('Keine Spacecrafts für die angegebene Auswahl gefunden'));
        }

        return array_values($spacecrafts);
    }

    /**
     * @return array<Spacecraft>
     */
    private function loadForAllPlayers(bool $includeShips, bool $includeStations, GameControllerInterface $game): array
    {
        $spacecrafts = [];

        if ($includeShips) {
            foreach ($this->shipRepository->getByUserIdAbove(UserConstants::USER_FIRST_ID) as $ship) {
                $spacecrafts[$ship->getId()] = $ship;
            }
        }

        if ($includeStations) {
            foreach ($this->stationRepository->getByUserIdAbove(UserConstants::USER_FIRST_ID) as $station) {
                $spacecrafts[$station->getId()] = $station;
            }
        }

        if ($spacecrafts === []) {
            $game->getInfo()->addInformation(_('Keine Spieler-Spacecrafts gefunden'));
        }

        return array_values($spacecrafts);
    }

    private function chargeSpacecraft(Spacecraft $spacecraft, string $target, bool $isMax, ?int $value, bool $override): bool
    {
        $wrapper = $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft);

        switch ($target) {
            case self::TARGET_EPS:
                $eps = $wrapper->getEpsSystemData();
                if ($eps === null) {
                    return false;
                }
                $eps->setEps($this->determineTargetValue($isMax, $value, $eps->getMaxEps(), $override))->update();
                return true;

            case self::TARGET_WARPDRIVE:
                $warpdrive = $wrapper->getWarpDriveSystemData();
                if ($warpdrive === null) {
                    return false;
                }
                $warpdrive->setWarpDrive($this->determineTargetValue($isMax, $value, $warpdrive->getMaxWarpdrive(), $override))->update();
                return true;

            case self::TARGET_SHIELDS:
                if (!$spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::SHIELDS)) {
                    return false;
                }
                $spacecraft->getCondition()->setShield($this->determineTargetValue($isMax, $value, $spacecraft->getMaxShield(), $override));
                $this->spacecraftRepository->save($spacecraft);
                return true;

            case self::TARGET_WARPCORE:
                $reactor = $wrapper->getReactorWrapper();
                if ($reactor === null) {
                    return false;
                }
                $reactor->setLoad($this->determineTargetValue($isMax, $value, $this->getCurrentReactorCapacity($spacecraft, $reactor), $override));
                return true;

            case self::TARGET_BATTERY:
                $eps = $wrapper->getEpsSystemData();
                if ($eps === null) {
                    return false;
                }
                $eps->setBattery($this->determineTargetValue($isMax, $value, $this->getCurrentMaxBattery($eps), $override))->update();
                return true;

            default:
                return false;
        }
    }

    private function determineTargetValue(bool $isMax, ?int $value, int $maxValue, bool $override): int
    {
        if ($isMax) {
            return $maxValue;
        }

        if ($value === null) {
            return 0;
        }

        return $override ? $value : min($value, $maxValue);
    }

    private function getCurrentReactorCapacity(Spacecraft $spacecraft, ReactorWrapperInterface $reactor): int
    {
        $systemType = $reactor->get()->getSystemType();

        return (int) ceil($reactor->getCapacity() * $spacecraft->getSpacecraftSystem($systemType)->getStatus() / 100);
    }

    private function getCurrentMaxBattery(EpsSystemData $eps): int
    {
        return min($eps->getMaxBattery(), (int) ceil($eps->getMaxEps() / 3));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
