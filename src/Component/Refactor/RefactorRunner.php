<?php

declare(strict_types=1);

namespace Stu\Component\Refactor;

use Stu\Orm\Entity\ModuleInterface;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Spacecraft\Buildplan\BuildplanSignatureCreationInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\SpacecraftBuildplanInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class RefactorRunner
{
    private LoggerUtilInterface $logger;

    public function __construct(
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
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
        foreach ($this->spacecraftBuildplanRepository->findAll() as $buildplan) {
            $calculatedSignature = $this->calculateSignature($buildplan);

            $buildplan->setSignature($calculatedSignature);
            $this->spacecraftBuildplanRepository->save($buildplan);
        }
    }

    private function removeBuildplanDuplicates(): void
    {
        $buildplanCount = 0;
        $shipCount = 0;

        /** @var null|SpacecraftBuildplanInterface */
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
                $this->spacecraftBuildplanRepository->delete($buildplan);
            }
        }

        $this->logger->logf('removed %d buildplan duplicates (%d ships affected)', $buildplanCount, $shipCount);
    }

    private function calculateSignature(SpacecraftBuildplanInterface $buildplan): string
    {
        $modules = $buildplan
            ->getModulesOrdered()
            ->map(fn(BuildplanModuleInterface $buildplanModule): ModuleInterface => $buildplanModule->getModule())
            ->toArray();

        $crewUsage = $buildplan->getUser()->isNpc() ? 0 : $buildplan->getCrew();

        return $this->buildplanSignatureCreation->createSignature(
            $modules,
            $crewUsage
        );
    }

    /** @return array<SpacecraftBuildplanInterface> */
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
                    SpacecraftBuildplan::class
                )
            )
            ->getResult();
    }
}
