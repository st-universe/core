<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ColonyCorrector implements ColonyCorrectorInterface
{
    private ColonyRepositoryInterface $colonyRepository;

    private EntityManagerInterface $entityManager;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ColonyRepositoryInterface $colonyRepository,
        EntityManagerInterface $entityManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->colonyRepository = $colonyRepository;
        $this->entityManager = $entityManager;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function correct(bool $doDump = true): void
    {
        $this->loggerUtil->init('CoCo', LoggerEnum::LEVEL_ERROR);

        $database = $this->entityManager->getConnection();

        foreach ($this->colonyRepository->getColonized() as $colony) {
            $colonyId = $colony->getId();

            $worker = (int) $database->fetchOne(
                'SELECT SUM(a.bev_use) FROM stu_buildings a LEFT
                    JOIN stu_colonies_fielddata scf on a.id = scf.buildings_id
                    WHERE scf.aktiv = 1 AND scf.colonies_id = :colonyId',
                ['colonyId' => $colonyId]
            );
            $housing = (int) $database->fetchOne(
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

            $max_free = max(0, $housing - $worker);

            if (
                $this->check($worker, $colony->getWorkers(), $colony, 'setWorkers', 'worker')
                || $this->check($housing, $colony->getMaxBev(), $colony, 'setMaxBev', 'housing')
                || $this->check($storage, $colony->getMaxStorage(), $colony, 'setMaxStorage', 'storage')
                || $this->check($eps, $colony->getMaxEps(), $colony, 'setMaxEps', 'eps')
                || $this->checkWorkless($max_free, $colony)
            ) {
                $this->colonyRepository->save($colony);
            }
        }

        $this->entityManager->flush();
    }

    private function check(int $expected, int $actual, ColonyInterface $colony, string $method, string $description): bool
    {
        if ($expected !== $actual) {
            $colony->$method($expected);

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

    private function checkWorkless(int $maxFree,  ColonyInterface $colony): bool
    {
        $actual = $colony->getWorkless();

        if ($maxFree < $actual) {
            $colony->setWorkless(min($maxFree, $actual));

            $this->loggerUtil->log(sprintf(
                'maxFreeWorkless of colonyId %d: expected: %d, actual: %d',
                $colony->getId(),
                $maxFree,
                $actual
            ));

            return true;
        }

        return false;
    }
}