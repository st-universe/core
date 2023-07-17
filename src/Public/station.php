<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
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

    /**
     * @todo Remove merging of ship and station actions
     */
    $dic->get(GameControllerInterface::class)->main(
        'station',
        array_merge($dic->get('SHIP_ACTIONS'), $dic->get('STATION_ACTIONS')),
        $dic->get('STATION_VIEWS')
    );

    $em->commit();
});
