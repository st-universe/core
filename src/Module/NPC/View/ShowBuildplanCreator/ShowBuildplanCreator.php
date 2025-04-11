<?php

declare(strict_types=1);

namespace Stu\Module\NPC\View\ShowBuildplanCreator;

use Override;
use request;
use RuntimeException;
use Stu\Component\Spacecraft\ModuleSpecialAbilityEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;

final class ShowBuildplanCreator implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_BUILDPLAN_CREATOR';

    public function __construct(
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private UserRepositoryInterface $userRepository,
        private ModuleRepositoryInterface $moduleRepository,
        private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository
    ) {}

    #[Override]
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
            $allRumps = iterator_to_array($this->spacecraftRumpRepository->getList());
            $filteredRumps = array_filter($allRumps, fn($rump) => $rump->getNpcBuildable() === true);

            $game->setTemplateVar('SHIP_RUMPS', $filteredRumps);

            if ($rumpId > 0) {
                $rump = $this->spacecraftRumpRepository->find($rumpId);
                if ($rump === null) {
                    throw new RuntimeException(sprintf('rumpId %d does not exist', $rumpId));
                }

                $isRumpInFiltered = false;
                foreach ($filteredRumps as $filteredRump) {
                    if ($filteredRump->getId() === $rumpId) {
                        $isRumpInFiltered = true;
                        break;
                    }
                }

                if (!$isRumpInFiltered) {
                    $game->addInformation('Dieser Rumpf darf nicht gebaut werden!');
                    return;
                }

                $game->setTemplateVar('RUMP_ID', $rumpId);
                $game->setTemplateVar('MODULE_SELECTION', true);

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

                if ($rump->getCategoryId() !== SpacecraftRumpEnum::SHIP_CATEGORY_STATION) {
                    $moduleTypes[] = SpacecraftModuleTypeEnum::WARPDRIVE;
                }

                $game->setTemplateVar('MODULE_TYPES', $moduleTypes);

                $mod_level = $this->shipRumpModuleLevelRepository->getByShipRump($rump->getId());
                if ($mod_level === null) {
                    throw new RuntimeException(sprintf('no module level for rumpId: %d', $rump->getId()));
                }

                $availableModules = [];
                $mandatoryModules = [];

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

                    $shipRumpRole = $rump->getShipRumpRole();
                    if ($shipRumpRole === null) {
                        throw new RuntimeException(sprintf('No ship rump role found for rump %d', $rump->getId()));
                    }

                    $modules = $this->moduleRepository->getByTypeAndLevel(
                        $moduleTypeId,
                        $shipRumpRole->getId(),
                        range($min_level, $max_level)
                    );

                    $modules = iterator_to_array($modules);

                    usort($modules, function ($a, $b) {
                        return $a->getId() <=> $b->getId();
                    });

                    $availableModules[$moduleTypeId] = $modules;



                    $mandatoryModules[$moduleTypeId] = $mod_level->{'getModuleMandatory' . $moduleTypeId}() > 0;
                }

                $game->setTemplateVar('AVAILABLE_MODULES', $availableModules);
                $game->setTemplateVar('MANDATORY_MODULES', $mandatoryModules);
                $game->setTemplateVar('SPECIAL_MODULES', $this->moduleRepository->getBySpecialTypeIds(ModuleSpecialAbilityEnum::getValueArray()));
            }
        } else {
            $npcList = iterator_to_array($this->userRepository->getNpcList());
            $nonNpcList = iterator_to_array($this->userRepository->getNonNpcList());
            $allUsers = array_merge($npcList, $nonNpcList);
            $game->setTemplateVar('ALL_USERS', $allUsers);
        }
    }
}
