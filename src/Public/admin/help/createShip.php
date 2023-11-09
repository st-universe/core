<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Stu\Config\Init;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Ship\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

@session_start();

require_once __DIR__ . '/../../../../vendor/autoload.php';

Init::run(function (ContainerInterface $dic): void {
    $db = $dic->get(EntityManagerInterface::class);

    $db->beginTransaction();

    $dic->get(GameControllerInterface::class)->sessionAndAdminCheck();

    $buildplanRepo = $dic->get(ShipBuildplanRepositoryInterface::class);
    $torpedoTypeRepo = $dic->get(TorpedoTypeRepositoryInterface::class);
    $userRepo = $dic->get(UserRepositoryInterface::class);
    $shipCreator = $dic->get(ShipCreatorInterface::class);
    $shipRepo = $dic->get(ShipRepositoryInterface::class);
    $crewCreator = $dic->get(CrewCreatorInterface::class);
    $shipCrewRepo = $dic->get(ShipCrewRepositoryInterface::class);
    $mapRepo = $dic->get(MapRepositoryInterface::class);
    $torpedoManager = $dic->get(ShipTorpedoManagerInterface::class);

    $userId = request::indInt('userId');
    $buildplanId = request::indInt('buildplanId');
    $torptypeId = request::indInt('torptypeId');
    $noTorps = request::indInt('noTorps');

    if ($torptypeId > 0 || $noTorps) {
        $plan = $buildplanRepo->find($buildplanId);
        $layerId = request::postIntFatal('layer');
        $cx = request::postIntFatal('cx');
        $cy = request::postIntFatal('cy');
        $shipcount = request::postIntFatal('shipcount');
        for ($i = 0; $i < $shipcount; $i++) {
            $wrapper = $shipCreator->createBy($userId, $plan->getRump()->getId(), $plan->getId());
            $ship = $wrapper->get();

            $ship->setMap($mapRepo->getByCoordinates($layerId, $cx, $cy));

            $reactor = $wrapper->getReactorWrapper();
            if ($reactor !== null) {
                $reactor->setLoad($reactor->getCapacity());
            }

            $ship->setShield($ship->getMaxShield());

            $epsSystem = $wrapper->getEpsSystemData();
            $epsSystem
                ->setEps($epsSystem->getMaxEps())
                ->setBattery($epsSystem->getMaxBattery())
                ->update();
            $warpdrive = $wrapper->getWarpDriveSystemData();
            if ($warpdrive !== null) {
                $warpdrive->setWarpDrive($warpdrive->getMaxWarpdrive())->update();
            }

            if ($torptypeId > 0) {
                $torp_obj = $torpedoTypeRepo->find($torptypeId);
                $torpedoManager->changeTorpedo($wrapper, $ship->getMaxTorpedos(), $torp_obj);
            }

            $shipRepo->save($ship);
            $db->flush();

            for ($j = 1; $j <= $plan->getCrew(); $j++) {
                $crewAssignment = $crewCreator->create($userId);
                $crewAssignment->setShip($ship);
                $shipCrewRepo->save($crewAssignment);
            }
            $db->flush();
        }
        echo $shipcount . ' Schiff(e) erstellt, mit Wurstblinkern!';
    } elseif ($buildplanId > 0) {
        printf(
            '<form action="" method="post">
            <input type="hidden" name="userId" value="%d" />
            <input type="hidden" name="buildplanId" value="%d" />
            <input type="hidden" name="layer" value="%d" />
            <input type="hidden" name="cx" value="%d" />
            <input type="hidden" name="cy" value="%d" />
            <input type="hidden" name="shipcount" value="%d" />
            ',
            $userId,
            $buildplanId,
            request::postIntFatal('layer'),
            request::postIntFatal('cx'),
            request::postIntFatal('cy'),
            request::postIntFatal('shipcount')
        );
        $plan = $buildplanRepo->find($buildplanId);
        $possibleTorpedoTypes = $torpedoTypeRepo->getByLevel($plan->getRump()->getTorpedoLevel());
        if ($possibleTorpedoTypes === []) {
            printf(
                '<input type="hidden" name="noTorps" value="1" />
                Schiff kann keine Torpedos tragen'
            );
        } else {
            foreach ($possibleTorpedoTypes as $torpType) {
                printf(
                    '<input type="radio" name="torptypeId" value="%d" />%s<br />',
                    $torpType->getId(),
                    $torpType->getName()
                );
            }
        }
        printf(
            '<br /><br />
            <input type="submit" value="Schiff erstellen" /></form>'
        );
    } elseif ($userId > 0) {
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
                Koordinaten<br /><input type="text" size="3" name="layer" value="1"/> | <input type="text" size="3" name="cx" /> | <input type="text" size="3" name="cy" /><br />
                Anzahl<br /><input type="text" size="3" name="shipcount" value="1"/><br /><br />
                <input type="submit" value="weiter zu Torpedo-Auswahl" /></form>'
        );
    } else {
        foreach ($userRepo->getNpcList() as $user) {
            printf(
                '<a href="?userId=%d">%s</a><br />',
                $user->getId(),
                $user->getName()
            );
        }
        foreach ($userRepo->getNonNpcList() as $user) {
            printf(
                '<a href="?userId=%d">%s</a><br />',
                $user->getId(),
                $user->getName()
            );
        }
    }

    $db->commit();
});
