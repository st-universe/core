<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Closure;
use Doctrine\ORM\EntityManagerInterface;
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
            $storage = (int) $database->fetchOne(
                'SELECT SUM(a.lager) FROM stu_buildings a LEFT
                    JOIN stu_colonies_fielddata scf on a.id = scf.buildings_id
                    WHERE scf.aktiv <= 1 AND scf.colonies_id = :colonyId',
                ['colonyId' => $colonyId]
            );
            $eps = (int) $database->fetchOne(
                'SELECT SUM(a.eps) FROM stu_buildings a LEFT
                    JOIN stu_colonies_fielddata scf on a.id = scf.buildings_id
                    WHERE scf.aktiv <= 1 AND scf.colonies_id = :colonyId',
                ['colonyId' => $colonyId]
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
}
