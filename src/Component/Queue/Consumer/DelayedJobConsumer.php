<?php

declare(strict_types=1);

namespace Stu\Component\Queue\Consumer;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Process\BuildingJobFinishProcessInterface;
use Stu\Component\Process\TerraformingJobFinishProcessInterface;
use Stu\Component\Queue\Driver\DelayedJobDriverInterface;
use Stu\Component\Queue\Message\MessageTransformatorInterface;
use Stu\Component\Queue\Message\TransformableMessageInterface;
use Stu\Component\Queue\Message\Type\BuildingJobProcessMessageInterface;
use Stu\Component\Queue\Message\Type\MessageTypeEnum;
use Stu\Component\Queue\Message\Type\TerraformingJobProcessMessage;
use Stu\Component\Queue\Processor\DelayedJobProcessorInterface;
use Stu\Component\Queue\QueueRouteEnum;

final class DelayedJobConsumer implements DelayedJobConsumerInterface
{
    private DelayedJobDriverInterface $delayedJobDriver;

    private MessageTransformatorInterface $messageTransformator;

    private BuildingJobFinishProcessInterface $buildingJobFinishProcess;

    private EntityManagerInterface $entityManager;

    private DelayedJobProcessorInterface $delayedJobProcessor;

    private TerraformingJobFinishProcessInterface $terraformingJobFinishProcess;

    public function __construct(
        DelayedJobDriverInterface $delayedJobDriver,
        MessageTransformatorInterface $messageTransformator,
        BuildingJobFinishProcessInterface $buildingJobFinishProcess,
        EntityManagerInterface $entityManager,
        DelayedJobProcessorInterface $delayedJobProcessor,
        TerraformingJobFinishProcessInterface $terraformingJobFinishProcess
    ) {
        $this->delayedJobDriver = $delayedJobDriver;
        $this->messageTransformator = $messageTransformator;
        $this->buildingJobFinishProcess = $buildingJobFinishProcess;
        $this->entityManager = $entityManager;
        $this->delayedJobProcessor = $delayedJobProcessor;
        $this->terraformingJobFinishProcess = $terraformingJobFinishProcess;
    }

    public function consume(): void {
        $this->delayedJobProcessor->registerProcessor(
            MessageTypeEnum::BUILDING_JOB,
            function(BuildingJobProcessMessageInterface $message): void {
                $this->entityManager->beginTransaction();

                $this->buildingJobFinishProcess->work($message);

                $this->entityManager->commit();
            }
        );
        $this->delayedJobProcessor->registerProcessor(
            MessageTypeEnum::TERRAFORMING_JOB,
            function(TerraformingJobProcessMessage $message): void {
                $this->entityManager->beginTransaction();

                $this->terraformingJobFinishProcess->work($message);

                $this->entityManager->commit();
            }
        );

        $this->delayedJobDriver->consume(
            'delayed_processes',
            'delayed_process_consumer_' . getmypid(),
            function(TransformableMessageInterface $message): void {
                $this->delayedJobProcessor->process($message);
            },
            QueueRouteEnum::DELAYED_JOB
        );
    }
}
