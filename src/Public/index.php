<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Stu\Component\Game\ModuleEnum;
use Stu\Component\Player\Register\RegistrationReferralTrackerInterface;
use Stu\Config\Init;
use Stu\Module\Control\GameControllerInterface;

/**
 * @deprecated Session handling should be part of the application
 */
@session_start();

require_once __DIR__ . '/../../vendor/autoload.php';

Init::run(function (ContainerInterface $dic): void {
    $em = $dic->get(EntityManagerInterface::class);
    $em->beginTransaction();

    $redirectTarget = $dic->get(RegistrationReferralTrackerInterface::class)->captureFromRequest();
    if ($redirectTarget !== null) {
        $em->commit();

        header(sprintf('Location: %s', $redirectTarget), true, 302);
        return;
    }

    $dic->get(GameControllerInterface::class)->main(
        ModuleEnum::INDEX,
        false
    );

    $em->commit();
});
