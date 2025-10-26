<?php

namespace Stu\Module\Maintenance;

use Stu\Component\History\HistoryTypeEnum;
use Stu\Component\Map\MapEnum;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\PirateRound;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\PirateRoundRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;



final class BeginPirateRound implements MaintenanceHandlerInterface
{
    public const array FORBIDDEN_ADMIN_AREAS = [
        MapEnum::ADMIN_REGION_SUPERPOWER_CENTRAL,
        MapEnum::ADMIN_REGION_SUPERPOWER_PERIPHERAL
    ];

    public function __construct(
        private PirateRoundRepositoryInterface $pirateRoundRepository,
        private UserRepositoryInterface $userRepository,
        private ShipRepositoryInterface $shipRepository,
        private EntryCreatorInterface $entryCreator,
        private LayerRepositoryInterface $layerRepository,
        private MapRepositoryInterface $mapRepository
    ) {}


    #[\Override]
    public function handle(): void
    {
        $lastRound = $this->getLastPirateRound();

        if ($lastRound === null) {
            return;
        }

        $endTime = $lastRound->getEndTime();

        if ($endTime !== null && $this->isBetween45And46DaysAgo($endTime)) {
            $this->handleOldRoundAndCreateNew();
            return;
        }
    }

    private function getLastPirateRound(): ?PirateRound
    {
        $allRounds = $this->pirateRoundRepository->findBy([], ['id' => 'DESC'], 1);

        return empty($allRounds) ? null : $allRounds[0];
    }

    private function isBetween45And46DaysAgo(int $timestamp): bool
    {
        $fortyFiveDaysAgo = time() - (45 * 24 * 60 * 60);
        $fortySixDaysAgo = time() - (46 * 24 * 60 * 60);

        return $timestamp <= $fortyFiveDaysAgo && $timestamp > $fortySixDaysAgo;
    }

    private function handleOldRoundAndCreateNew(): void
    {
        $this->createHistoryEntry('Kazon-Piraten wurden im Tullamore Trench gesichtet');

        $this->createNewPirateRound();
    }

    private function createNewPirateRound(): void
    {
        $prestigeValue = $this->calculatePrestigeValue();

        $pirateRound = $this->pirateRoundRepository->prototype();
        $pirateRound->setStart(time());
        $pirateRound->setEndTime(null);
        $pirateRound->setMaxPrestige($prestigeValue);
        $pirateRound->setActualPrestige($prestigeValue);
        $pirateRound->setFactionWinner(null);

        $this->pirateRoundRepository->save($pirateRound);
    }

    private function calculatePrestigeValue(): int
    {
        $ships = $this->shipRepository->findAll();

        $totalPrestige = 0;
        foreach ($ships as $ship) {
            $rumpPrestige = $ship->getRump()->getPrestige();
            if ($rumpPrestige > 0) {
                $totalPrestige += $rumpPrestige;
            }
        }


        return (int) round($totalPrestige / 3);
    }

    private function createHistoryEntry(string $text): void
    {
        $nooneUser = $this->userRepository->find(UserConstants::USER_NOONE);
        $kazonUser = $this->userRepository->find(UserConstants::USER_NOONE);

        if ($nooneUser === null || $kazonUser === null) {
            return;
        }

        $randomLocation = $this->getRandomMapLocation();

        $this->entryCreator->createEntry(
            HistoryTypeEnum::OTHER,
            $text,
            $nooneUser->getId(),
            $kazonUser->getId(),
            $randomLocation
        );
    }

    private function getRandomMapLocation(): Map
    {
        $defaultLayer = $this->layerRepository->getDefaultLayer();

        do {
            $map = $this->mapRepository->getRandomPassableUnoccupiedWithoutDamage($defaultLayer);
        } while (
            in_array($map->getAdminRegionId(), self::FORBIDDEN_ADMIN_AREAS)
            || $map->getFieldType()->hasEffect(FieldTypeEffectEnum::NO_PIRATES)
        );

        return $map;
    }
}
