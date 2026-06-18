<?php

declare(strict_types=1);

namespace Stu\Module\NPC\View\ShowBuildplanCreator;

use request;
use RuntimeException;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\ModuleSpecialAbilityEnum;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ShowBuildplanCreator implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_BUILDPLAN_CREATOR';

    public function __construct(
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private UserRepositoryInterface $userRepository,
        private ModuleRepositoryInterface $moduleRepository,
        private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository,
        private SpacecraftCrewCalculatorInterface $shipCrewCalculator
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = request::getInt('userId');
        $rumpId = request::getInt('rumpId');

        $game->setTemplateFile('html/npc/buildplanCreator.twig');
        $game->appendNavigationPart('/npc/index.php?SHOW_BUILDPLAN_CREATOR=1', 'Bauplan erstellen');
        $game->setPageTitle('Bauplan erstellen');

        if ($userId > 0) {
            $selectedUser = $this->userRepository->find($userId);
            if ($selectedUser === null) {
                throw new RuntimeException(sprintf('userId %d does not exist', $userId));
            }

            $game->setTemplateVar('USER_ID', $userId);
            $game->setTemplateVar('SELECTED_USER', $selectedUser);
            $allRumps = iterator_to_array($this->spacecraftRumpRepository->getList());
            $filteredRumps = array_filter($allRumps, fn ($rump): bool => $rump->getNpcBuildable() === true);

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
                    $game->getInfo()->addInformation('Dieser Rumpf darf nicht gebaut werden!');
                    return;
                }

                $game->setTemplateVar('RUMP_ID', $rumpId);
                $game->setTemplateVar('RUMP', $rump);
                $game->setTemplateVar('MODULE_SELECTION', true);

                $moduleTypes = array_values(array_filter(
                    SpacecraftModuleTypeEnum::getModuleSelectorOrder(),
                    fn (SpacecraftModuleTypeEnum $moduleType): bool =>
                    $moduleType !== SpacecraftModuleTypeEnum::SPECIAL
                        && (
                            $moduleType !== SpacecraftModuleTypeEnum::WARPDRIVE
                            || $rump->getCategoryId() !== SpacecraftRumpCategoryEnum::STATION
                        )
                ));

                $mod_level = $this->shipRumpModuleLevelRepository->getByShipRump($rump);
                if ($mod_level === null) {
                    throw new RuntimeException(sprintf('no module level for rumpId: %d', $rump->getId()));
                }

                /** @var array<SpacecraftModuleTypeEnum> $availableModuleTypes */
                $availableModuleTypes = [];
                /** @var array<int, array<Module>> $availableModules */
                $availableModules = [];
                /** @var array<int, bool> $mandatoryModules */
                $mandatoryModules = [];
                /** @var array<int, int> $moduleCrew */
                $moduleCrew = [];
                /** @var array<int, array<string>> $moduleEffects */
                $moduleEffects = [];
                /** @var array<int, string> $moduleLevelClasses */
                $moduleLevelClasses = [];

                foreach ($moduleTypes as $moduleType) {
                    $moduleTypeId = $moduleType->value;

                    if (
                        $mod_level->getDefaultLevel($moduleType) === 0
                        && !$mod_level->isMandatory($moduleType)
                    ) {
                        continue;
                    }

                    $availableModuleTypes[] = $moduleType;
                    $min_level = $mod_level->getMinimumLevel($moduleType);
                    $max_level = $mod_level->getMaximumLevel($moduleType);
                    $defaultLevel = $mod_level->getDefaultLevel($moduleType);

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

                    usort(
                        $modules,
                        fn (Module $a, Module $b): int => [$a->getLevel(), $a->getId()] <=> [$b->getLevel(), $b->getId()]
                    );

                    $availableModules[$moduleTypeId] = $modules;
                    foreach ($modules as $module) {
                        $moduleCrew[$module->getId()] = $this->getNeededCrew($module, $selectedUser, $rump);
                        $moduleEffects[$module->getId()] = $this->getModuleEffects($module, $rump);
                        $moduleLevelClasses[$module->getId()] = $this->getModuleLevelClass($defaultLevel, $module);
                    }

                    $mandatoryModules[$moduleTypeId] = $mod_level->isMandatory($moduleType);
                }

                $specialModules = $this->moduleRepository->getBySpecialTypeIds(ModuleSpecialAbilityEnum::getValueArray());
                usort(
                    $specialModules,
                    fn (Module $a, Module $b): int => $a->getId() <=> $b->getId()
                );

                $allowedSpecialModuleIds = [];
                foreach ($this->moduleRepository->getBySpecialTypeAndRumpWithoutHost(SpacecraftModuleTypeEnum::SPECIAL, $rump->getId()) as $module) {
                    $allowedSpecialModuleIds[$module->getId()] = true;
                }

                $specialModuleAllowed = [];
                foreach ($specialModules as $module) {
                    $moduleCrew[$module->getId()] = $this->getNeededCrew($module, $selectedUser, $rump);
                    $moduleEffects[$module->getId()] = $this->getModuleEffects($module, $rump);
                    $specialModuleAllowed[$module->getId()] = $allowedSpecialModuleIds[$module->getId()] ?? false;
                }

                $game->setTemplateVar('MODULE_TYPES', $availableModuleTypes);
                $game->setTemplateVar('AVAILABLE_MODULES', $availableModules);
                $game->setTemplateVar('MANDATORY_MODULES', $mandatoryModules);
                $game->setTemplateVar('SPECIAL_MODULES', $specialModules);
                $game->setTemplateVar('SPECIAL_MODULE_ALLOWED', $specialModuleAllowed);
                $game->setTemplateVar('MODULE_CREW', $moduleCrew);
                $game->setTemplateVar('MODULE_EFFECTS', $moduleEffects);
                $game->setTemplateVar('MODULE_LEVEL_CLASSES', $moduleLevelClasses);
                $game->setTemplateVar('MIN_CREW_COUNT', $rump->getBaseValues()->getBaseCrew());
                $game->setTemplateVar('MAX_CREW_COUNT', $this->shipCrewCalculator->getMaxCrewCountByRump($rump));
                $game->setTemplateVar('SPECIAL_SLOTS', $rump->getBaseValues()->getSpecialSlots());
            }
        } else {
            $npcList = iterator_to_array($this->userRepository->getNpcList());
            $nonNpcList = iterator_to_array($this->userRepository->getNonNpcList());
            $allUsers = array_merge($npcList, $nonNpcList);
            $game->setTemplateVar('ALL_USERS', $allUsers);
        }
    }

    private function getNeededCrew(Module $module, User $user, SpacecraftRump $rump): int
    {
        return $module->getCrewByFactionAndRumpLvl($user->getFaction(), $rump);
    }

    private function getModuleLevelClass(int $defaultLevel, Module $module): string
    {
        if ($defaultLevel > $module->getLevel()) {
            return 'module_positive';
        }
        if ($defaultLevel < $module->getLevel()) {
            return 'module_negative';
        }

        return '';
    }

    /**
     * @return array<string>
     */
    private function getModuleEffects(Module $module, SpacecraftRump $rump): array
    {
        if ($module->getType() === SpacecraftModuleTypeEnum::SPECIAL) {
            $effects = [];
            foreach ($module->getSpecials() as $special) {
                $effects[] = $special->getName();
            }

            return $effects;
        }

        $wrapper = $module->getType()->getModuleRumpWrapperCallable()($rump, null);
        $value = $wrapper->getValue($module);

        $moduleType = $module->getType();
        if ($moduleType === SpacecraftModuleTypeEnum::HULL) {
            return [sprintf('Hüllenstärke: %d', $value)];
        }
        if ($moduleType === SpacecraftModuleTypeEnum::SHIELDS) {
            return [sprintf('Schildkapazität: %d', $value)];
        }
        if ($moduleType === SpacecraftModuleTypeEnum::EPS) {
            return [
                sprintf('Energiespeicher: %d', $value),
                sprintf('Ersatzbatterie: %d', $wrapper->getSecondValue($module))
            ];
        }
        if ($moduleType === SpacecraftModuleTypeEnum::IMPULSEDRIVE) {
            return [sprintf('Ausweichchance: %d%%', $value)];
        }
        if ($moduleType === SpacecraftModuleTypeEnum::REACTOR) {
            return [
                sprintf('Reaktorleistung: %d', $value),
                sprintf('Reaktorkapazität: %d', $wrapper->getSecondValue($module))
            ];
        }
        if ($moduleType === SpacecraftModuleTypeEnum::COMPUTER) {
            return [sprintf('Trefferchance: %d%%', $value)];
        }
        if ($moduleType === SpacecraftModuleTypeEnum::PHASER) {
            return [sprintf('Basisschaden (abstrakt): %d', $value)];
        }
        if ($moduleType === SpacecraftModuleTypeEnum::TORPEDO) {
            return [sprintf('Schilddurchdringung: %s%%', $this->formatPercentage($value / 100))];
        }
        if ($moduleType === SpacecraftModuleTypeEnum::SENSOR) {
            return [sprintf('Sensorenreichweite: %d', $value)];
        }

        return [sprintf('Warpreichweite: %d', $value)];
    }

    private function formatPercentage(float $value): string
    {
        return rtrim(rtrim(sprintf('%.2F', $value), '0'), '.');
    }
}
