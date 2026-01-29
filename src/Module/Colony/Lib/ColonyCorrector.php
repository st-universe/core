<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Closure;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Logging\LogLevelEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Repository\ColonyRepositoryInterface;

class ColonyCorrector implements ColonyCorrectorInterface
{
    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private readonly ColonyRepositoryInterface $colonyRepository,
        private readonly EntityManagerInterface $entityManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[\Override]
    public function correct(bool $doDump = true): void
    {
        $this->loggerUtil->init('CoCo', LogLevelEnum::ERROR);

        $database = $this->entityManager->getConnection();

        foreach ($this->colonyRepository->getColonized() as $colony) {
            $colonyId = $colony->getId();

            $worker = (int) $database->fetchOne(
                'SELECT SUM(a.bev_use) FROM stu_buildings a LEFT
                    JOIN stu_colonies_fielddata scf on a.id = scf.buildings_id
                    WHERE scf.aktiv = 1 AND scf.colonies_id = :colonyId',
                ['colonyId' => $colonyId]
            );
            $activeHousing = (int) $database->fetchOne(
                'SELECT SUM(a.bev_pro) FROM stu_buildings a LEFT
                    JOIN stu_colonies_fielddata scf on a.id = scf.buildings_id
                    WHERE scf.aktiv = 1 AND scf.colonies_id = :colonyId',
                ['colonyId' => $colonyId]
            );

            $hasUndergroundLogistics = $this->hasUndergroundLogisticsProduction($database, $colonyId);

            $storage = (int) $database->fetchOne(
                'SELECT SUM(a.lager) FROM stu_buildings a 
                    LEFT JOIN stu_colonies_fielddata scf on a.id = scf.buildings_id
                    WHERE scf.aktiv <= 1 
                    AND scf.colonies_id = :colonyId
                    AND (
                        a.id NOT IN (
                            SELECT DISTINCT bc.buildings_id 
                            FROM stu_buildings_commodity bc 
                            WHERE bc.commodity_id = :logisticsCommodityId 
                            AND bc.count < 0
                        )
                        OR :hasLogistics = 1
                    )',
                [
                    'colonyId' => $colonyId,
                    'logisticsCommodityId' => CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS,
                    'hasLogistics' => $hasUndergroundLogistics ? 1 : 0
                ]
            );

            $eps = (int) $database->fetchOne(
                'SELECT SUM(a.eps) FROM stu_buildings a 
                    LEFT JOIN stu_colonies_fielddata scf on a.id = scf.buildings_id
                    WHERE scf.aktiv <= 1 
                    AND scf.colonies_id = :colonyId
                    AND (
                        a.id NOT IN (
                            SELECT DISTINCT bc.buildings_id 
                            FROM stu_buildings_commodity bc 
                            WHERE bc.commodity_id = :logisticsCommodityId 
                            AND bc.count < 0
                        )
                        OR :hasLogistics = 1
                    )',
                [
                    'colonyId' => $colonyId,
                    'logisticsCommodityId' => CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS,
                    'hasLogistics' => $hasUndergroundLogistics ? 1 : 0
                ]
            );

            if (
                $this->check($worker, $colony->getWorkers(), $colony, function (ColonyChangeable $cc, $expected): void {
                    $cc->setWorkers($expected);
                }, 'worker')
                || $this->check($activeHousing, $colony->getChangeable()->getMaxBev(), $colony, function (ColonyChangeable $cc, $expected): void {
                    $cc->setMaxBev($expected);
                }, 'housing')
                || $this->check($storage, $colony->getMaxStorage(), $colony, function (ColonyChangeable $cc, $expected): void {
                    $cc->setMaxStorage($expected);
                }, 'storage')
                || $this->check($eps, $colony->getMaxEps(), $colony, function (ColonyChangeable $cc, $expected): void {
                    $cc->setMaxEps($expected);
                }, 'eps')
            ) {
                $this->colonyRepository->save($colony);
            }
        }

        $this->entityManager->flush();
    }

    /** @param Closure(ColonyChangeable, int): void $modifier */
    private function check(int $expected, int $actual, Colony $colony, Closure $modifier, string $description): bool
    {
        if ($expected !== $actual) {
            $modifier($colony->getChangeable(), $expected);

            $this->loggerUtil->log(sprintf(
                '%s of colonyId %d: expected: %d, actual: %d',
                $description,
                $colony->getId(),
                $expected,
                $actual
            ));

            return true;
        }

        return false;
    }

    private function hasUndergroundLogisticsProduction(Connection $database, int $colonyId): bool
    {
        $count = (int) $database->fetchOne(
            'SELECT COUNT(*) FROM stu_colonies_fielddata scf
                JOIN stu_buildings_commodity bc ON bc.buildings_id = scf.buildings_id
                WHERE scf.colonies_id = :colonyId
                AND scf.aktiv = 1
                AND bc.commodity_id = :logisticsCommodityId
                AND bc.count > 0',
            [
                'colonyId' => $colonyId,
                'logisticsCommodityId' => CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS
            ]
        );

        return $count > 0;
    }
}
