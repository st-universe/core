<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\ShipModule\ModuleSpecialAbilityEnum;
use Stu\Module\ShipModule\ModuleTypeDescriptionMapper;
use Stu\Orm\Entity\ShipBuildplan;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

@session_start();

require_once __DIR__ . '/../../../Config/Bootstrap.php';

$db = $container->get(EntityManagerInterface::class);

$db->beginTransaction();

$container->get(GameControllerInterface::class)->main(
    $container->get('ADMIN_ACTIONS'),
    $container->get('ADMIN_VIEWS'),
    true,
    true
);

$shipRumpRepo = $container->get(ShipRumpRepositoryInterface::class);
$shipRumpModuleLevelRepo = $container->get(ShipRumpModuleLevelRepositoryInterface::class);
$moduleRepo = $container->get(ModuleRepositoryInterface::class);
$buildplanRepo = $container->get(ShipBuildplanRepositoryInterface::class);
$buildplanModuleRepo = $container->get(BuildplanModuleRepositoryInterface::class);
$userRepo = $container->get(UserRepositoryInterface::class);

$userId = request::indInt('userId');
$rumpId = request::indInt('rumpId');

if ($rumpId !== 0) {
    $rump = $shipRumpRepo->find($rumpId);

    $specialModuleTypes = [
        ModuleSpecialAbilityEnum::MODULE_SPECIAL_CLOAK,
        ModuleSpecialAbilityEnum::MODULE_SPECIAL_RPG,
    ];

    $moduleTypes = [
        ShipModuleTypeEnum::MODULE_TYPE_HULL,
        ShipModuleTypeEnum::MODULE_TYPE_SHIELDS,
        ShipModuleTypeEnum::MODULE_TYPE_EPS,
        ShipModuleTypeEnum::MODULE_TYPE_IMPULSEDRIVE,
        ShipModuleTypeEnum::MODULE_TYPE_WARPCORE,
        ShipModuleTypeEnum::MODULE_TYPE_COMPUTER,
        ShipModuleTypeEnum::MODULE_TYPE_PHASER,
        ShipModuleTypeEnum::MODULE_TYPE_TORPEDO,
    ];
    $moduleList = request::postArray('mod');
    $moduleSpecialList = request::postArray('special_mod');

    if (count($moduleList) === count($moduleTypes)) {
        $user = $userRepo->find($userId);

        $signature = ShipBuildplan::createSignature(array_merge($moduleList, $moduleSpecialList));
        $plan = $buildplanRepo->getByUserShipRumpAndSignature($userId, $rump->getId(), $signature);

        if ($plan === null) {
            $planname = sprintf(
                _('Bauplan %s %s'),
                $rump->getName(),
                date('d.m.Y H:i')
            );
            $plan = $buildplanRepo->prototype();
            $plan->setUser($user);
            $plan->setRump($rump);
            $plan->setName($planname);
            $plan->setSignature($signature);
            $plan->setBuildtime(0);
            $plan->setCrew($rump->getCrew100P());
            $plan->setCrewPercentage(100);

            $buildplanRepo->save($plan);

            foreach ($moduleList as $moduleId) {
                $module = $moduleRepo->find($moduleId);

                $mod = $buildplanModuleRepo->prototype();
                $mod->setModuleType($module->getType());
                $mod->setBuildplanId($plan->getId());
                $mod->setModule($module);

                $buildplanModuleRepo->save($mod);
            }

            $moduleSpecialList = request::postArray('special_mod');

            foreach ($moduleSpecialList as $moduleId) {
                $module = $moduleRepo->find($moduleId);

                $mod = $buildplanModuleRepo->prototype();
                $mod->setModuleType($module->getType());
                $mod->setBuildplanId($plan->getId());
                $mod->setModule($module);

                $buildplanModuleRepo->save($mod);
            }
        }

        echo 'Bauplan angelegt';

    } else {

        printf(
            '<form action="" method="post">
            <input type="hidden" name="userId" value="%d">',
            $userId
        );

        foreach ($moduleTypes as $moduleTypeId) {

            printf(
                '<div>Modul: %s</div>',
                ModuleTypeDescriptionMapper::getDescription($moduleTypeId)
            );

            $mod_level = $shipRumpModuleLevelRepo->getByShipRump(
                $rump->getId()
            );

            $min_level = $mod_level->{'getModuleLevel' . $moduleTypeId . 'Min'}();
            $max_level = $mod_level->{'getModuleLevel' . $moduleTypeId . 'Max'}();

            $modules = $moduleRepo->getByTypeAndLevel(
                $moduleTypeId,
                $rump->getShipRumpRole()->getId(),
                range($min_level, $max_level)
            );

            foreach ($modules as $module) {
                printf(
                    '<div>
                    <input type="radio" name="mod[%d]" value="%d" /> %s
                </div>',
                    $moduleTypeId,
                    $module->getId(),
                    $module->getName()
                );
            }

            echo '<br /><br />';
        }

        $specialModules = $moduleRepo->getBySpecialTypeIds($specialModuleTypes);

        foreach ($specialModules as $module) {
            printf(
                '<div>
                    <input type="checkbox" name="special_mod[%d]" value="%d" /> %s
                </div>',
                $module->getId(),
                $module->getId(),
                $module->getName()
            );
        }

        printf(
            '<br /><input type="submit" value="Bauplan erstellen" /></form>'
        );
    }

} else {
    if ($userId > 0) {
        foreach ($shipRumpRepo->getList() as $shipRump) {
            printf(
                '<div><a href="?rumpId=%d&userId=%d"><img src="/assets/ships/%s.gif" /> %s</a></div>',
                $shipRump->getId(),
                $userId,
                $shipRump->getId(),
                $shipRump->getName()
            );
        }
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
