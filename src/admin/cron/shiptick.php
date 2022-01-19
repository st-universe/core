<?php

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Module\Tick\Ship\ShipTickManagerInterface;

require_once __DIR__ . '/../../Config/Bootstrap.php';

$entityManager = $container->get(EntityManagerInterface::class);

$entityManager->beginTransaction();

try {
    $container->get(ShipTickManagerInterface::class)->work();
    $entityManager->flush();
    $entityManager->commit();
} catch (Exception $e) {
    $entityManager->rollback();

    $emailSender = $container->get(FailureEmailSenderInterface::class);
    $emailSender->sendMail(
        "stu shiptick failure",
        sprintf(
            "Current system time: %s\nThe shiptick cron caused an error:\n\n%s\n\n%s",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getTraceAsString()
        )
    );

    throw $e;
}
