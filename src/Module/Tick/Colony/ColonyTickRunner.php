<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Colony;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Tick\AbstractTickRunner;
use Throwable;
use Ubench;

/**
 * Executes the colony tick (energy and commodity production, etc)
 */
final class ColonyTickRunner extends AbstractTickRunner
{
    private EntityManagerInterface $entityManager;
    private ColonyTickManagerInterface $colonyTickManager;
    private FailureEmailSenderInterface $failureEmailSender;
    private Ubench $benchmark;
    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        EntityManagerInterface $entityManager,
        ColonyTickManagerInterface $colonyTickManager,
        FailureEmailSenderInterface $failureEmailSender,
        Ubench $benchmark,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->entityManager = $entityManager;
        $this->colonyTickManager = $colonyTickManager;
        $this->failureEmailSender = $failureEmailSender;
        $this->benchmark = $benchmark;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function run(int $batchGroup, int $batchGroupCount): void
    {
        $this->entityManager->beginTransaction();

        try {
            $this->colonyTickManager->work($batchGroup, $batchGroupCount);

            $this->entityManager->flush();
            $this->entityManager->commit();

            $this->loggerUtil->init('COLOTICK', LoggerEnum::LEVEL_WARNING);
            $this->logBenchmarkResult();
        } catch (Throwable $e) {
            $this->entityManager->rollback();

            $this->failureEmailSender->sendMail(
                'stu colonytick failure',
                sprintf(
                    "Current system time: %s\nThe colonytick cron (group %d/%d) caused an error:\n\n%s\n\n%s",
                    date('Y-m-d H:i:s'),
                    $batchGroup,
                    $batchGroupCount,
                    $e->getMessage(),
                    $e->getTraceAsString()
                )
            );

            throw $e;
        }
    }

    protected function getBenchmark(): Ubench
    {
        return $this->benchmark;
    }

    protected function getLoggerUtil(): LoggerUtilInterface
    {
        return $this->loggerUtil;
    }
}
