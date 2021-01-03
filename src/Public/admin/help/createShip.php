<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

@session_start();

require_once __DIR__ . '/../../../Config/Bootstrap.php';

$db = $container->get(EntityManagerInterface::class);

$db->beginTransaction();

$buildplanRepo = $container->get(ShipBuildplanRepositoryInterface::class);
$userRepo = $container->get(UserRepositoryInterface::class);
$shipCreator = $container->get(ShipCreatorInterface::class);
$shipRepo = $container->get(ShipRepositoryInterface::class);
$crewCreator = $container->get(CrewCreatorInterface::class);

$userId = request::indInt('userId');
$buildplanId = request::indInt('buildplanId');

if ($buildplanId > 0) {
    $plan = $buildplanRepo->find($buildplanId);
    $cx = request::postIntFatal('cx');
    $cy = request::postIntFatal('cy');

    $ship = $shipCreator->createBy($userId, $plan->getRump()->getId(), $plan->getId());
    $ship->setCx($cx);
    $ship->setCy($cy);
    $ship->setEps($ship->getMaxEps());
    $ship->setWarpcoreLoad($ship->getWarpcoreCapacity());

    $shipRepo->save($ship);

    for ($i = 1; $i <= $plan->getCrew(); $i++) {
        $crewCreator->create($userId);
    }
    $crewCreator->createShipCrew($ship);

    echo 'Schiff erstellt';

} else {
    if ($userId > 0) {
        $buildplans = $buildplanRepo->getByUser($userId);

        printf(
            '<form action="" method="post">
            <input type="hidden" name="userId" value="%d" />',
            $userId
        );

        foreach ($buildplans as $plan) {
            printf(
                '<input type="radio" name="buildplanId" value="%d" />%s<br />',
                $plan->getId(),
                $plan->getName()
            );
        }

        printf(
            '<br /><br />
            Koordinaten<br /><input type="text" size="3" name="cx" /> | <input type="text" size="3" name="cy" /><br /><br />
            <input type="submit" value="Schiff erstellen" /></form>'
        );
    } else {
        foreach ($userRepo->getNpcList() as $user) {
            printf(
                '<a href="?userId=%d">%s</a><br />',
                $user->getId(),
                $user->getUserName()
            );
        }
    }
}

$db->commit();
