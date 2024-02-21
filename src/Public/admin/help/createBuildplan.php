<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Config\Init;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\ShipModule\ModuleSpecialAbilityEnum;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipBuildplan;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

@session_start();

require_once __DIR__ . '/../../../../vendor/autoload.php';

Init::run(function (ContainerInterface $dic): void {
    $db = $dic->get(EntityManagerInterface::class);

    $db->beginTransaction();

    $dic->get(GameControllerInterface::class)->sessionAndAdminCheck();

    $shipRumpRepo = $dic->get(ShipRumpRepositoryInterface::class);
    $shipRumpModuleLevelRepo = $dic->get(ShipRumpModuleLevelRepositoryInterface::class);
    $moduleRepo = $dic->get(ModuleRepositoryInterface::class);
    $buildplanRepo = $dic->get(ShipBuildplanRepositoryInterface::class);
    $buildplanModuleRepo = $dic->get(BuildplanModuleRepositoryInterface::class);
    $userRepo = $dic->get(UserRepositoryInterface::class);
    $shipCrewCalculator = $dic->get(ShipCrewCalculatorInterface::class);

    $userId = request::indInt('userId');
    $rumpId = request::indInt('rumpId');

    if ($rumpId !== 0) {
        $rump = $shipRumpRepo->find($rumpId);
        if ($rump === null) {
            throw new RuntimeException(sprintf('rumpId %d does not exist!', $rumpId));
        }

        $mod_level = $shipRumpModuleLevelRepo->getByShipRump(
            $rump->getId()
        );
        $specialModuleTypes = [
            ModuleSpecialAbilityEnum::MODULE_SPECIAL_CLOAK,
            ModuleSpecialAbilityEnum::MODULE_SPECIAL_RPG,
            ModuleSpecialAbilityEnum::MODULE_SPECIAL_TACHYON_SCANNER,
            ModuleSpecialAbilityEnum::MODULE_SPECIAL_TROOP_QUARTERS,
            ModuleSpecialAbilityEnum::MODULE_SPECIAL_SUBSPACE_FIELD_SENSOR,
            ModuleSpecialAbilityEnum::MODULE_SPECIAL_MATRIX_SENSOR,
            ModuleSpecialAbilityEnum::MODULE_SPECIAL_ASTRO_LABORATORY,
            ModuleSpecialAbilityEnum::MODULE_SPECIAL_TORPEDO_STORAGE,
            ModuleSpecialAbilityEnum::MODULE_SPECIAL_SHUTTLE_RAMP,
            ModuleSpecialAbilityEnum::MODULE_SPECIAL_TRANSWARP_COIL,
            ModuleSpecialAbilityEnum::MODULE_SPECIAL_HIROGEN_TRACKER,
            ModuleSpecialAbilityEnum::MODULE_SPECIAL_THOLIAN_WEB
        ];
        $moduleTypes = [
            ShipModuleTypeEnum::HULL,
            ShipModuleTypeEnum::SHIELDS,
            ShipModuleTypeEnum::EPS,
            ShipModuleTypeEnum::IMPULSEDRIVE,
            ShipModuleTypeEnum::REACTOR,
            ShipModuleTypeEnum::COMPUTER,
            ShipModuleTypeEnum::PHASER,
            ShipModuleTypeEnum::TORPEDO,
            ShipModuleTypeEnum::SENSOR
        ];

        if ($rump->getCategoryId() !== ShipRumpEnum::SHIP_CATEGORY_STATION) {
            $moduleTypes[] = ShipModuleTypeEnum::WARPDRIVE;
        }

        $moduleList = request::postArray('mod');
        $moduleSpecialList = request::postArray('special_mod');
        if (count($moduleList) >= $mod_level->getMandatoryModulesCount()) {
            $user = $userRepo->find($userId);
            if ($user === null) {
                throw new RuntimeException('userId %d does not exist', $userId);
            }

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

                $buildplanRepo->save($plan);
                $db->flush();

                /** @var array<int, ModuleInterface> */
                $modules = [];

                foreach ($moduleList as $moduleId) {
                    $module = $moduleRepo->find($moduleId);
                    if ($module === null) {
                        throw new RuntimeException(sprintf('moduleId %d does not exist', $moduleId));
                    }

                    $mod = $buildplanModuleRepo->prototype();
                    $mod->setModuleType($module->getType());
                    $mod->setBuildplan($plan);
                    $mod->setModule($module);

                    $modules[$moduleId] = $module;

                    $buildplanModuleRepo->save($mod);
                }

                $moduleSpecialList = request::postArray('special_mod');
                foreach ($moduleSpecialList as $moduleId) {
                    $module = $moduleRepo->find($moduleId);
                    if ($module === null) {
                        throw new RuntimeException(sprintf('moduleId %d does not exist', $moduleId));
                    }

                    $mod = $buildplanModuleRepo->prototype();
                    $mod->setModuleType($module->getType());
                    $mod->setBuildplan($plan);
                    $mod->setModule($module);
                    $mod->setModuleSpecial(ModuleSpecialAbilityEnum::getHash($module->getSpecials()));

                    $modules[$moduleId] = $module;

                    $buildplanModuleRepo->save($mod);
                }


                $plan->setCrew($shipCrewCalculator->getCrewUsage($modules, $rump, $user));
                $buildplanRepo->save($plan);
                $db->flush();
            }

            echo 'Bauplan angelegt';
        } else {
            printf(
                '<form action="" method="post">
            <input type="hidden" name="userId" value="%d">',
                $userId
            );

            foreach ($moduleTypes as $moduleType) {
                $mod_level = $shipRumpModuleLevelRepo->getByShipRump(
                    $rump->getId()
                );

                $moduleTypeId = $moduleType->value;

                if (
                    $mod_level->{'getModuleLevel' . $moduleTypeId}() === 0
                    && $mod_level->{'getModuleMandatory' . $moduleTypeId}() === 0
                ) {
                    continue;
                }

                printf(
                    '<div>Modul: %s</div>',
                    $moduleType->getDescription()
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
    } elseif ($userId > 0) {
        foreach ($shipRumpRepo->getList() as $shipRump) {
            printf(
                '<div><a href="?rumpId=%d&userId=%d"><img src="/assets/ships/%s.png" /> %s</a></div>',
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
