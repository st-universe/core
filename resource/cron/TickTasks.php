<?php

declare(strict_types=1);

use Crunz\Schedule;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Stu\Component\Admin\Notification\FailureEmailSenderInterface;
use Stu\Config\Init;
use Stu\Module\Tick\Colony\ColonyTickManagerInterface;

$schedule = new Schedule();

$schedule
    ->run(function(): void {
        Init::run(function (ContainerInterface $container): void {
            $entityManager = $container->get(EntityManagerInterface::class);

            $entityManager->beginTransaction();

            try {
                $tickManager = $container->get(ColonyTickManagerInterface::class);
                $tickManager->work(1);
                $entityManager->flush();
                $entityManager->commit();
            } catch (Exception $e) {
                $entityManager->rollback();

                $emailSender = $container->get(FailureEmailSenderInterface::class);
                $emailSender->sendMail(
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
        });
    })
    ->everyThreeHours()
    ->between('12:00', '0:00')
    ->description('ColonyTick');

return $schedule;
