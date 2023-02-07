<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Ship;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Tick\TickRunnerInterface;
use Throwable;

/**
 * Executes the shiptick
 */
final class ShipTickRunner implements TickRunnerInterface
{
    /** @var int */
    private const ATTEMPTS = 5;

    private EntityManagerInterface $entityManager;

    private FailureEmailSenderInterface $failureEmailSender;

    private ShipTickManagerInterface $shipTickManager;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        EntityManagerInterface $entityManager,
        FailureEmailSenderInterface $failureEmailSender,
        ShipTickManagerInterface $shipTickManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        $this->entityManager = $entityManager;
        $this->failureEmailSender = $failureEmailSender;
        $this->shipTickManager = $shipTickManager;
    }

    public function run(): void
    {
        $this->loggerUtil->init('mail', LoggerEnum::LEVEL_ERROR);

        /**
         * There seems to be some sort of locking-problem. Because of that, the tick gets retried several times
         */
        for ($i = 1; $i <= self::ATTEMPTS; $i++) {
            $exception = $this->execute($this->loggerUtil);

            if ($exception === null) {
                break;
            } else {
                // logging problem
                $this->loggerUtil->log(sprintf(
                    "Shiptick caused an exception. Remaing tries: %d\nException-Message: %s\nException-Trace: %s",
                    self::ATTEMPTS - $i,
                    $exception->getMessage(),
                    $exception->getTraceAsString()
                ));

                // sending email if no remaining tries left
                if ($i === self::ATTEMPTS) {
                    $this->failureEmailSender->sendMail(
                        'stu shiptick failure',
                        sprintf(
                            "Current system time: %s\nThe shiptick cron caused an error:\n\n%s\n\n%s",
                            date('Y-m-d H:i:s'),
                            $exception->getMessage(),
                            $exception->getTraceAsString()
                        )
                    );

                    throw $exception;
                }
            }
        }
    }

    private function execute(LoggerUtilInterface $loggerUtil): ?Throwable
    {
        try {
            $this->entityManager->beginTransaction();

            $this->shipTickManager->work();

            $this->entityManager->flush();
            $this->entityManager->commit();

            $this->loggerUtil->init('COLOTICK', LoggerEnum::LEVEL_WARNING);
            $this->loggerUtil->log('shiptick finished');

            return null;
        } catch (Throwable $e) {
            $loggerUtil->log('  rollback');

            $this->entityManager->rollback();

            return $e;
        }
    }
}
