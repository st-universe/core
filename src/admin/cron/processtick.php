<?php

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Tick\Process\ProcessTickInterface;

require_once __DIR__ . '/../../Config/Bootstrap.php';


$entityManager = $container->get(EntityManagerInterface::class);

$entityManager->beginTransaction();

try {
    /**
     * @var ProcessTickInterface[] $handlerList
     */
    $handlerList = $container->get('process_tick_handler');

    foreach ($handlerList as $process) {
        $process->work();
    }

    $entityManager->flush();
    $entityManager->commit();
} catch (Exception $e) {
    $entityManager->rollback();

    $emailSender = $container->get(FailureEmailSenderInterface::class);
    $emailSender->sendMail(
        "stu processtick failure",
        sprintf(
            "Current system time: %s\nThe processtick cron caused an error:\n\n%s\n\n%s",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getTraceAsString()
        )
    );

    throw $e;
}
