<?php

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Module\Tick\Maintenance\Maintenance;
use Stu\Orm\Repository\GameConfigRepositoryInterface;

require_once __DIR__ . '/../../Config/Bootstrap.php';


$entityManager = $container->get(EntityManagerInterface::class);

$entityManager->beginTransaction();

try {
    $maintenance = new Maintenance(
        $container->get(GameConfigRepositoryInterface::class),
        $container->get('maintenance_handler')
    );
    $maintenance->handle();
    $entityManager->flush();
    $entityManager->commit();
} catch (Exception $e) {
    $entityManager->rollback();

    $emailSender = $container->get(FailureEmailSenderInterface::class);
    $emailSender->sendMail(
        "stu maintenancetick failure",
        sprintf(
            "Current system time: %s\nThe maintenancetick cron caused an error:\n\n%s\n\n%s",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $e->getTraceAsString()
        )
    );

    throw $e;
}
