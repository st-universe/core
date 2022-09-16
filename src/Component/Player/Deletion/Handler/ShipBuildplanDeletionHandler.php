<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

final class ShipBuildplanDeletionHandler implements PlayerDeletionHandlerInterface
{
    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private EntityManagerInterface $entityManager;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        EntityManagerInterface  $entityManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->entityManager = $entityManager;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function delete(UserInterface $user): void
    {
        $this->loggerUtil->init('delete', LoggerEnum::LEVEL_ERROR);

        $this->loggerUtil->log(sprintf('user has %d buildplans', count($this->shipBuildplanRepository->getByUser($user->getId()))));

        $this->shipBuildplanRepository->truncateByUser($user->getId());
        $this->entityManager->flush();

        $this->loggerUtil->log(sprintf('user has %d buildplans', count($this->shipBuildplanRepository->getByUser($user->getId()))));
    }
}
