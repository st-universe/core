<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ColonyCorrector implements ColonyCorrectorInterface
{
    private ColonyRepositoryInterface $colonyRepository;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ColonyRepositoryInterface $colonyRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->colonyRepository = $colonyRepository;
        $this->entityManager = $entityManager;
    }

    public function correct(bool $doDump = true): void
    {
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
                    WHERE scf.colonies_id = :colonyId',
                ['colonyId' => $colonyId]
            );
            $eps = (int) $database->fetchOne(
                'SELECT SUM(a.eps) FROM stu_buildings a LEFT
                    JOIN stu_colonies_fielddata scf on a.id = scf.buildings_id
                    WHERE scf.colonies_id = :colonyId',
                ['colonyId' => $colonyId]
            );

            $max_free = max(0, $housing - $worker);

            if (
                $worker !== $colony->getWorkers() ||
                $housing !== $colony->getMaxBev() ||
                $storage !== $colony->getMaxStorage() ||
                $eps !== $colony->getMaxEps() ||
                $max_free < $colony->getWorkless()
            ) {
                if ($doDump) {
                    var_dump([
                        ['worker' => $worker, 'actual' => $colony->getWorkers()],
                        ['housing' => $housing, 'actual' => $colony->getMaxBev()],
                        ['storage' => $storage, 'actual' => $colony->getMaxStorage()],
                        ['eps' => $eps, 'actual' => $colony->getMaxEps()],
                        ['max_free' => $max_free, 'actual' => $colony->getWorkless()],
                    ]);
                }

                $colony->setWorkers($worker);
                $colony->setMaxBev($housing);
                $colony->setMaxStorage($storage);
                $colony->setMaxEps($eps);
                $colony->setWorkless(min($max_free, $colony->getWorkless()));

                $this->colonyRepository->save($colony);
            }
        }

        $this->entityManager->flush();
    }
}
