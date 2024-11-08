<?php

declare(strict_types=1);

namespace Stu\Module\NPC\View\ShowBuildplanCreator;

use request;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Module\ShipModule\ModuleSpecialAbilityEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;

final class ShowBuildplanCreator implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_BUILDPLAN_CREATOR';

    private ShipRumpRepositoryInterface $shipRumpRepository;
    private UserRepositoryInterface $userRepository;
    private ModuleRepositoryInterface $moduleRepository;
    private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository;

    public function __construct(
        ShipRumpRepositoryInterface $shipRumpRepository,
        UserRepositoryInterface $userRepository,
        ModuleRepositoryInterface $moduleRepository,
        ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository
    ) {
        $this->shipRumpRepository = $shipRumpRepository;
        $this->userRepository = $userRepository;
        $this->moduleRepository = $moduleRepository;
        $this->shipRumpModuleLevelRepository = $shipRumpModuleLevelRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = request::getInt('userId');
        $rumpId = request::getInt('rumpId');

        $game->setTemplateFile('html/npc/buildplanCreator.twig');
        $game->appendNavigationPart('/npc/index.php?SHOW_BUILDPLAN_CREATOR=1', 'Bauplan erstellen');
        $game->setPageTitle('Bauplan erstellen');

        if ($userId > 0) {
            $selectedUser = $this->userRepository->find($userId);
            $game->setTemplateVar('USER_ID', $userId);
            $game->setTemplateVar('SELECTED_USER', $selectedUser);
            $game->setTemplateVar('SHIP_RUMPS', $this->shipRumpRepository->getList());

            if ($rumpId > 0) {
                $rump = $this->shipRumpRepository->find($rumpId);
                $game->setTemplateVar('RUMP_ID', $rumpId);
                $game->setTemplateVar('MODULE_SELECTION', true);

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

                $game->setTemplateVar('MODULE_TYPES', $moduleTypes);

                $mod_level = $this->shipRumpModuleLevelRepository->getByShipRump($rump->getId());

                $availableModules = [];
                foreach ($moduleTypes as $moduleType) {
                    $moduleTypeId = $moduleType->value;
                    if (
                        $mod_level->{'getModuleLevel' . $moduleTypeId}() === 0
                        && $mod_level->{'getModuleMandatory' . $moduleTypeId}() === 0
                    ) {
                        continue;
                    }

                    $min_level = $mod_level->{'getModuleLevel' . $moduleTypeId . 'Min'}();
                    $max_level = $mod_level->{'getModuleLevel' . $moduleTypeId . 'Max'}();

                    $availableModules[$moduleTypeId] = $this->moduleRepository->getByTypeAndLevel(
                        $moduleTypeId,
                        $rump->getShipRumpRole()->getId(),
                        range($min_level, $max_level)
                    );
                }
                $game->setTemplateVar('AVAILABLE_MODULES', $availableModules);

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
                    ModuleSpecialAbilityEnum::MODULE_SPECIAL_THOLIAN_WEB,
                    ModuleSpecialAbilityEnum::MODULE_SPECIAL_BUSSARD_COLLECTOR,
                    ModuleSpecialAbilityEnum::MODULE_SPECIAL_AGGREGATION_SYSTEM
                ];
                $game->setTemplateVar('SPECIAL_MODULES', $this->moduleRepository->getBySpecialTypeIds($specialModuleTypes));
            }
        } else {
            $allUsers = array_merge(
                $this->userRepository->getNpcList(),
                $this->userRepository->getNonNpcList()
            );
            $game->setTemplateVar('ALL_USERS', $allUsers);
        }
    }
}