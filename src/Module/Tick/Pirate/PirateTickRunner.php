<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Pirate;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\Tick\TickRunnerInterface;
use Stu\Module\Tick\TransactionTickRunnerInterface;
use Throwable;

/**
 * Executes the pirate tick
 */
final class PirateTickRunner implements TickRunnerInterface
{
    private const int ATTEMPTS = 5;

    private const string TICK_DESCRIPTION = "piratetick";

    private PirateLoggerInterface $loggerUtil;

    public function __construct(
        private TransactionTickRunnerInterface $transactionTickRunner,
        private PirateTickInterface $pirateTick,
        private FailureEmailSenderInterface $failureEmailSender,
        private StuConfigInterface $stuConfig,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        private EntityManagerInterface $entityManager
    ) {
        $this->loggerUtil = $loggerUtilFactory->getPirateLogger();
    }

    #[Override]
    public function run(int $batchGroup, int $batchGroupCount): void
    {
        if ($this->transactionTickRunner->isGameStateReset()) {
            return;
        }

        if (!$this->stuConfig
            ->getGameSettings()
            ->getPirateSettings()
            ->isPirateTickActive()) {
            return;
        }

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
                    "%s caused an exception. Remaing tries: %d\nException-Message: %s\nException-Trace: %s",
                    self::TICK_DESCRIPTION,
                    self::ATTEMPTS - $i,
                    $exception->getMessage(),
                    $exception->getTraceAsString()
                ));

                // sending email if no remaining tries left
                if ($i === self::ATTEMPTS) {
                    $this->failureEmailSender->sendMail(
                        sprintf('stu %s failure', self::TICK_DESCRIPTION),
                        sprintf(
                            "Current system time: %s\nThe %s cron caused an error:\n\n%s\n\n%s",
                            date('Y-m-d H:i:s'),
                            self::TICK_DESCRIPTION,
                            $exception->getMessage(),
                            $exception->getTraceAsString()
                        )
                    );

                    throw $exception;
                }
            }
        }
    }

    private function execute(PirateLoggerInterface $logger): ?Throwable
    {
        try {
            $this->entityManager->beginTransaction();

            $this->pirateTick->work();

            $this->entityManager->flush();
            $this->entityManager->commit();

            return null;
        } catch (Throwable $e) {
            $logger->log('  rollback');
            $this->entityManager->rollback();

            return $e;
        }
    }
}
