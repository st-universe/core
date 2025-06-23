<?php

declare(strict_types=1);

use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Stu\Component\Spacecraft\Buildplan\BuildplanSignatureCreationInterface;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Config\Init;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Spacecraft\ModuleSpecialAbilityEnum;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

@session_start();

require_once __DIR__ . '/../../../../vendor/autoload.php';

Init::run(function (ContainerInterface $dic): void {
    $db = $dic->get(EntityManagerInterface::class);

    $db->beginTransaction();

    $dic->get(GameControllerInterface::class)->sessionAndAdminCheck();

    $shipRumpRepo = $dic->get(SpacecraftRumpRepositoryInterface::class);
    $shipRumpModuleLevelRepo = $dic->get(ShipRumpModuleLevelRepositoryInterface::class);
    $moduleRepo = $dic->get(ModuleRepositoryInterface::class);
    $buildplanRepo = $dic->get(SpacecraftBuildplanRepositoryInterface::class);
    $buildplanModuleRepo = $dic->get(BuildplanModuleRepositoryInterface::class);
    $userRepo = $dic->get(UserRepositoryInterface::class);
    $shipCrewCalculator = $dic->get(SpacecraftCrewCalculatorInterface::class);
    $buildplanSignatureCreation = $dic->get(BuildplanSignatureCreationInterface::class);

    $userId = request::indInt('userId');
    $rumpId = request::indInt('rumpId');

    if ($rumpId !== 0) {
        $rump = $shipRumpRepo->find($rumpId);
        if ($rump === null) {
            throw new RuntimeException(sprintf('rumpId %d does not exist!', $rumpId));
        }

        $mod_level = $shipRumpModuleLevelRepo->getByShipRump($rump);
        $moduleTypes = [
            SpacecraftModuleTypeEnum::HULL,
            SpacecraftModuleTypeEnum::SHIELDS,
            SpacecraftModuleTypeEnum::EPS,
            SpacecraftModuleTypeEnum::IMPULSEDRIVE,
            SpacecraftModuleTypeEnum::REACTOR,
            SpacecraftModuleTypeEnum::COMPUTER,
            SpacecraftModuleTypeEnum::PHASER,
            SpacecraftModuleTypeEnum::TORPEDO,
            SpacecraftModuleTypeEnum::SENSOR
        ];

        if ($rump->getCategoryId() !== SpacecraftRumpCategoryEnum::SHIP_CATEGORY_STATION) {
            $moduleTypes[] = SpacecraftModuleTypeEnum::WARPDRIVE;
        }

        $moduleList = request::postArray('mod');
        $moduleSpecialList = request::postArray('special_mod');
        if (count($moduleList) >= $mod_level->getMandatoryModulesCount()) {
            $user = $userRepo->find($userId);
            if ($user === null) {
                throw new RuntimeException('userId %d does not exist', $userId);
            }

            $signature = $buildplanSignatureCreation->createSignatureByModuleIds(array_merge($moduleList, $moduleSpecialList), 0);
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
                $mod_level = $shipRumpModuleLevelRepo->getByShipRump($rump);
                $moduleTypeId = $moduleType->value;

                if (
                    $mod_level->getDefaultLevel($moduleType) === 0
                    && !$mod_level->isMandatory($moduleType)
                ) {
                    continue;
                }

                printf(
                    '<div>Modul: %s</div>',
                    $moduleType->getDescription()
                );

                $min_level = $mod_level->getMinimumLevel($moduleType);
                $max_level = $mod_level->getMaximumLevel($moduleType);

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

            $specialModules = $moduleRepo->getBySpecialTypeIds(ModuleSpecialAbilityEnum::getValueArray());

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
