<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Ship;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Tick\AbstractTickRunner;
use Throwable;

/**
 * Executes the shiptick
 */
final class ShipTickRunner extends AbstractTickRunner
{
    /** @var int */
    private const ATTEMPTS = 5;

    private ShipTickManagerInterface $shipTickManager;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        GameControllerInterface $game,
        EntityManagerInterface $entityManager,
        FailureEmailSenderInterface $failureEmailSender,
        ShipTickManagerInterface $shipTickManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        parent::__construct($game, $entityManager, $failureEmailSender);

        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        $this->shipTickManager = $shipTickManager;
    }

    public function runWithResetCheck(int $batchGroup, int $batchGroupCount): void
    {
        if ($this->isGameStateReset()) {
            return;
        }

        $this->loggerUtil->init('mail', LoggerEnum::LEVEL_ERROR);

        /**
         * There seems to be some sort of locking-problem. Because of that, the tick gets retried several times
         */
        for ($i = 1; $i <= self::ATTEMPTS; $i++) {
            $exception = $this->execute($this->loggerUtil);

            if ($exception === null) {
                break;
            } else {

                $tickDescription = $this->getTickDescription();

                // logging problem
                $this->loggerUtil->log(sprintf(
                    "%s caused an exception. Remaing tries: %d\nException-Message: %s\nException-Trace: %s",
                    $tickDescription,
                    self::ATTEMPTS - $i,
                    $exception->getMessage(),
                    $exception->getTraceAsString()
                ));

                // sending email if no remaining tries left
                if ($i === self::ATTEMPTS) {
                    $this->failureEmailSender->sendMail(
                        sprintf('stu %s failure', $tickDescription),
                        sprintf(
                            "Current system time: %s\nThe %s cron caused an error:\n\n%s\n\n%s",
                            date('Y-m-d H:i:s'),
                            $tickDescription,
                            $exception->getMessage(),
                            $exception->getTraceAsString()
                        )
                    );

                    throw $exception;
                }
            }
        }
    }

    public function getTickDescription(): string
    {
        return "shiptick";
    }

    public function runInTransaction(int $batchGroup, int $batchGroupCount): void
    {
        //nothing to do here
    }

    private function execute(LoggerUtilInterface $loggerUtil): ?Throwable
    {
        try {
            $this->entityManager->beginTransaction();

            $this->shipTickManager->work();

            $this->entityManager->flush();
            $this->entityManager->commit();

            return null;
        } catch (Throwable $e) {
            $loggerUtil->log('  rollback');
            $this->entityManager->rollback();

            return $e;
        }
    }
}
