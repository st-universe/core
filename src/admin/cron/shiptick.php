<?php

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Tick\Ship\ShipTickManagerInterface;

require_once __DIR__ . '/../../Config/Bootstrap.php';

$loggerUtil = $container->get(LoggerUtilFactoryInterface::class)->getLoggerUtil();
$loggerUtil->init("mail", LoggerEnum::LEVEL_ERROR);

$entityManager = $container->get(EntityManagerInterface::class);

$remainingtries = 5;
while ($remainingtries > 0) {
    $remainingtries -= 1;
    $exception = tryTick($container, $entityManager);

    if ($exception === null) {
        break;
    } else {
        // logging problem
        $loggerUtil->log(sprintf(
            "Shiptick caused an exception. Remaing tries: %d",
            $remainingtries
        ));

        // sending email if no remaining tries left
        if ($remainingtries === 0) {
            $emailSender = $container->get(FailureEmailSenderInterface::class);
            $emailSender->sendMail(
                "stu shiptick failure",
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

function tryTick($container, $entityManager): ?Exception
{
    try {
        $entityManager->beginTransaction();

        $container->get(ShipTickManagerInterface::class)->work();
        $entityManager->flush();
        $entityManager->commit();

        return null;
    } catch (Exception $e) {
        $entityManager->rollback();

        return $e;
    }
}
