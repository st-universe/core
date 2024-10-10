<?php

declare(strict_types=1);

namespace Stu\Component\Refactor;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Ship\Buildplan\BuildplanSignatureCreationInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\ShipBuildplan;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class RefactorRunner
{
    private LoggerUtilInterface $logger;

    public function __construct(
        private ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        private BuildplanSignatureCreationInterface $buildplanSignatureCreation,
        private ShipRepositoryInterface $shipRepository,
        private EntityManagerInterface $entityManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getLoggerUtil(true);
    }

    public function refactor(): void
    {
        $this->recalculateAllBuildplanSignatures();
        $this->entityManager->flush();
        $this->removeBuildplanDuplicates();
    }

    private function recalculateAllBuildplanSignatures(): void
    {
        foreach ($this->shipBuildplanRepository->findAll() as $buildplan) {
            $calculatedSignature = $this->calculateSignature($buildplan);

            $buildplan->setSignature($calculatedSignature);
            $this->shipBuildplanRepository->save($buildplan);
        }
    }

    private function removeBuildplanDuplicates(): void
    {
        $buildplanCount = 0;
        $shipCount = 0;

        /** @var null|ShipBuildplanInterface */
        $lastBuildplan = null;
        foreach ($this->getBuildplansWithDuplicates() as $buildplan) {
            $buildplanUser = $buildplan->getUser();
            $buildplanRump = $buildplan->getRump();
            $buildplanSignature = $buildplan->getSignature();

            if (
                $lastBuildplan === null
                || $buildplanUser !== $lastBuildplan->getUser()
                || $buildplanRump !== $lastBuildplan->getRump()
                || $buildplanSignature !== $lastBuildplan->getSignature()
            ) {
                $lastBuildplan = $buildplan;
            } else {
                $buildplanCount++;
                foreach ($buildplan->getShiplist() as $ship) {
                    $ship->setBuildplan($lastBuildplan);
                    $this->shipRepository->save($ship);
                    $shipCount++;
                }

                $buildplan->getShiplist()->clear();
                $this->shipBuildplanRepository->delete($buildplan);
            }
        }

        $this->logger->logf('removed %d buildplan duplicates (%d ships affected)', $buildplanCount, $shipCount);
    }

    private function calculateSignature(ShipBuildplanInterface $buildplan): string
    {
        $modules = $buildplan
            ->getModules()
            ->map(fn(BuildplanModuleInterface $buildplanModule) => $buildplanModule->getModule())
            ->toArray();

        $crewUsage = $buildplan->getUser()->isNpc() ? 0 : $buildplan->getCrew();

        $signature = $this->buildplanSignatureCreation->createSignature(
            $modules,
            $crewUsage
        );

        return $signature;
    }

    /** @return array<ShipBuildplanInterface> */
    private function getBuildplansWithDuplicates(): array
    {
        return $this->entityManager
            ->createQuery(
                sprintf(
                    'SELECT bp FROM %1$s bp
                    WHERE EXISTS (SELECT bp2
                                    FROM %1$s bp2
                                    WHERE bp.signature = bp2.signature
                                    AND bp.rump_id = bp2.rump_id
                                    AND bp.user_id = bp2.user_id
                                    AND bp.id != bp2.id)
                    AND bp.signature IS NOT NULL
                    ORDER BY bp.user_id ASC, bp.rump_id ASC, bp.signature ASC, bp.id ASC',
                    ShipBuildplan::class
                )
            )
            ->getResult();
    }
}
