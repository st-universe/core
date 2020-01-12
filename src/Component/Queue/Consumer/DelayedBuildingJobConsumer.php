<?php

declare(strict_types=1);

namespace Stu\Component\Queue\Consumer;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Process\BuildingJobFinishProcessInterface;
use Stu\Component\Queue\Driver\DelayedJobDriverInterface;
use Stu\Component\Queue\Message\MessageTransformatorInterface;
use Stu\Component\Queue\Message\Type\BuildingJobProcessMessageInterface;
use Stu\Component\Queue\QueueRouteEnum;

final class DelayedBuildingJobConsumer implements DelayedBuildingJobConsumerInterface
{
    private DelayedJobDriverInterface $delayedJobDriver;

    private MessageTransformatorInterface $messageTransformator;

    private BuildingJobFinishProcessInterface $buildingJobFinishProcess;

    private EntityManagerInterface $entityManager;

    public function __construct(
        DelayedJobDriverInterface $delayedJobDriver,
        MessageTransformatorInterface $messageTransformator,
        BuildingJobFinishProcessInterface $buildingJobFinishProcess,
        EntityManagerInterface $entityManager
    ) {
        $this->delayedJobDriver = $delayedJobDriver;
        $this->messageTransformator = $messageTransformator;
        $this->buildingJobFinishProcess = $buildingJobFinishProcess;
        $this->entityManager = $entityManager;
    }

    public function consume(): void {
        $this->delayedJobDriver->consume(
            'delayed_building_processes',
            'delayed_building_process_consumer_' . getmypid(),
            function(BuildingJobProcessMessageInterface $message): void {
                $this->entityManager->beginTransaction();

                $this->buildingJobFinishProcess->work($message);

                $this->entityManager->commit();
            },
            QueueRouteEnum::DELAYED_BUILDING_JOB
        );
    }
}
