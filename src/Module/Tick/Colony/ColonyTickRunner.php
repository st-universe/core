<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Colony;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Tick\TickRunnerInterface;
use Throwable;

/**
 * Executes the colony tick (energy and commodity production, etc)
 */
final class ColonyTickRunner implements TickRunnerInterface
{
    // currently, there is just a single process - so hardcode it
    private const COLONY_TICK_ID = 1;

    private EntityManagerInterface $entityManager;
    private ColonyTickManagerInterface $colonyTickManager;
    private FailureEmailSenderInterface $failureEmailSender;
    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        EntityManagerInterface $entityManager,
        ColonyTickManagerInterface $colonyTickManager,
        FailureEmailSenderInterface $failureEmailSender,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->entityManager = $entityManager;
        $this->colonyTickManager = $colonyTickManager;
        $this->failureEmailSender = $failureEmailSender;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function run(): void
    {
        $this->entityManager->beginTransaction();

        try {
            $this->colonyTickManager->work(self::COLONY_TICK_ID);

            $this->entityManager->flush();
            $this->entityManager->commit();

            $this->loggerUtil->init('COLOTICK', LoggerEnum::LEVEL_WARNING);
            $this->loggerUtil->log('colonytick finished');
        } catch (Throwable $e) {
            $this->entityManager->rollback();

            $this->failureEmailSender->sendMail(
                'stu colonytick failure',
                sprintf(
                    "Current system time: %s\nThe colonytick cron caused an error:\n\n%s\n\n%s",
                    date('Y-m-d H:i:s'),
                    $e->getMessage(),
                    $e->getTraceAsString()
                )
            );

            throw $e;
        }
    }
}
