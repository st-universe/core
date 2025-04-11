<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\RetrofitShip;

use Override;
use request;
use RuntimeException;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Component\Spacecraft\Buildplan\BuildplanSignatureCreationInterface;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\SpacecraftModuleTypeEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Colony\View\ShowModuleScreen\ShowModuleScreen;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Spacecraft\ModuleSpecialAbilityEnum;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;

final class RetrofitShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_RETROFIT_SHIP';

    public function __construct(
        private ColonyFunctionManagerInterface $colonyFunctionManager,
        private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository,
        private ColonyLoaderInterface $colonyLoader,
        private BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        private ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository,
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private ModuleRepositoryInterface $moduleRepository,
        private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private StorageManagerInterface $storageManager,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private SpacecraftCrewCalculatorInterface $shipCrewCalculator,
        private ColonyRepositoryInterface $colonyRepository,
        private ShipRepositoryInterface $shipRepository,
        private BuildplanSignatureCreationInterface $buildplanSignatureCreation
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $mod = null;
        $colony = $this->colonyLoader->loadWithOwnerValidation(
            request::indInt('id'),
            $userId
        );

        $colonyId = $colony->getId();

        $rump = $this->spacecraftRumpRepository->find(request::indInt('rumpid'));
        if ($rump === null) {
            return;
        }

        $ship = $this->shipRepository->find(request::indInt('shipid'));
        if ($ship === null) {
            return;
        }

        $oldplan = $ship->getBuildplan();
        if ($oldplan === null) {
            return;
        }

        if ($ship->getBuildplan()) {
            if ($ship->getBuildplan() != $oldplan) {
                $game->addInformation(_('Das Schiff hat einen anderen Bauplan'));
                return;
            }
            if ($ship->getBuildplan()->getUser() !== $user) {
                $game->addInformation(_('Übernommene Schiffe können nicht umgerüstet werden'));
            }
        }

        $building_function = null;
        foreach ($this->shipRumpBuildingFunctionRepository->getByShipRump($rump) as $bfunc) {
            if ($this->colonyFunctionManager->hasActiveFunction($colony, $bfunc->getBuildingFunction())) {
                $building_function = $bfunc;
            }
        }
        if ($building_function === null) {
            $game->addInformation(_('Die Werft ist nicht aktiviert'));
            return;
        }
        $game->setView(ShowModuleScreen::VIEW_IDENTIFIER);

        if ($this->colonyShipQueueRepository->getAmountByColonyAndBuildingFunctionAndMode($colonyId, $building_function->getBuildingFunction(), 1) > 0) {
            $game->addInformation(_('In dieser Werft wird aktuell ein Schiff gebaut'));
            return;
        }

        if ($this->colonyShipQueueRepository->getAmountByColonyAndBuildingFunctionAndMode($colonyId, $building_function->getBuildingFunction(), 2) > 0) {
            $game->addInformation(_('In dieser Werft wird aktuell ein Schiff umgerüstet'));
            return;
        }

        if ($colony->getEps() < $rump->getEpsCost()) {
            $game->addInformationf(
                _('Zur Umrüstung wird %d Energie benötigt, es ist jedoch nur %d Energie vorhanden'),
                $rump->getEpsCost(),
                $colony->getEps()
            );
            return;
        }

        if ($colony->isBlocked()) {
            $game->addInformation(_('Schiffsumrüstung ist nicht möglich während die Kolonie blockiert wird'));
            return;
        }

        $moduleLevels = $this->shipRumpModuleLevelRepository->getByShipRump($rump->getId());
        if ($moduleLevels === null) {
            throw new RuntimeException(sprintf('no module level for rumpId: %d', $rump->getId()));
        }

        /** @var array<int, ModuleInterface> */
        $modules = [];

        /** @var array<int, array<int, ModuleInterface>> */
        $oldModulesOfType = [];

        foreach (SpacecraftModuleTypeEnum::getModuleSelectorOrder() as $moduleType) {
            $value = $moduleType->value;
            $module = request::postArray('mod_' . $value);

            $oldModulesOfType[$value] = $oldplan->getModulesByType($moduleType)->toArray();

            if (
                $moduleType != SpacecraftModuleTypeEnum::SPECIAL
                && $moduleLevels->{'getModuleMandatory' . $value}()
                && count($module) == 0
            ) {
                $game->addInformationf(
                    _('Es wurde kein Modul des Typs %s ausgewählt'),
                    $moduleType->getDescription()
                );
                return;
            }
            if ($moduleType->isSpecialSystemType()) {
                $specialCount = 0;
                foreach ($module as $id) {
                    $specialMod = $this->moduleRepository->find((int) $id);
                    if ($specialMod === null) {
                        throw new RuntimeException(sprintf('moduleId %d does not exist', $id));
                    }

                    $modules[$id] = $specialMod;
                    $specialCount++;
                }

                if ($specialCount > $rump->getSpecialSlots()) {
                    $game->addInformation(_('Mehr Spezial-Module als der Rumpf gestattet'));
                    return;
                }
                continue;
            }
            if (count($module) == 0 || current($module) == 0) {
                continue;
            }
            if (current($module) > 0) {
                $moduleId = (int) current($module);
                $mod = $this->moduleRepository->find($moduleId);
                if ($mod === null) {
                    throw new RuntimeException(sprintf('moduleId %d does not exist', $moduleId));
                }
            } elseif (!$moduleLevels->{'getModuleLevel' . $value}()) {
                return;
            }
            if ($mod === null) {
                throw new RuntimeException(sprintf('moduleId %d does not exist', (int)current($module)));
            }
            $modules[current($module)] = $mod;
        }

        $crewUsage = $this->shipCrewCalculator->getCrewUsage($modules, $rump, $user);
        if ($crewUsage > $this->shipCrewCalculator->getMaxCrewCountByRump($rump)) {
            $game->addInformation(_('Crew-Maximum wurde überschritten'));
            return;
        }

        $storage = $colony->getStorage();
        $modulesToLower = [];
        foreach ($modules as $module) {
            $isNewModule = !array_filter($oldModulesOfType[$module->getType()->value], function (ModuleInterface $oldModule) use ($module): bool {
                return $oldModule->getId() === $module->getId();
            });

            if ($isNewModule) {
                if (!$storage->containsKey($module->getCommodityId())) {
                    $game->addInformationf(_('Es wird 1 %s benötigt'), $module->getName());
                    return;
                }
                $selector = $this->colonyLibFactory->createModuleSelector(
                    $module->getType(),
                    $colony,
                    $rump,
                    $user
                );
                if (!array_key_exists($module->getId(), $selector->getAvailableModules())) {
                    return;
                }
                $modulesToLower[] = $module;
            }
        }



        foreach ($modulesToLower as $module) {
            $this->storageManager->lowerStorage($colony, $module->getCommodity(), 1);
        }

        $game->setView(ShowColony::VIEW_IDENTIFIER);


        $signature = $this->buildplanSignatureCreation->createSignature($modules, $crewUsage);
        $plan = $this->spacecraftBuildplanRepository->getByUserShipRumpAndSignature($userId, $rump->getId(), $signature);
        if ($plan == $oldplan) {
            $game->addInformation(_('Es wurden keine Änderungen ausgewählt'));
            return;
        }
        if ($plan === null) {
            $plannameFromRequest = request::indString('buildplanname');
            if (
                $plannameFromRequest !== false
                && $plannameFromRequest !== ''
                && $plannameFromRequest !== 'Bauplanname'
                && $plannameFromRequest !== $oldplan->getName()
            ) {
                $planname = $plannameFromRequest;
            } elseif ($plannameFromRequest === $oldplan->getName()) {
                $planname = $plannameFromRequest . " Retrofit";
            } else {
                $planname = sprintf(
                    _('Bauplan %s %s'),
                    $rump->getName(),
                    date('d.m.Y H:i')
                );
            }
            $game->addInformationf(
                _('Lege neuen Bauplan an: %s'),
                $planname
            );
            $plan = $this->spacecraftBuildplanRepository->prototype();
            $plan->setUser($game->getUser());
            $plan->setRump($rump);
            $plan->setName($planname);
            $plan->setSignature($signature);
            $plan->setBuildtime($rump->getBuildtime());
            $plan->setCrew($crewUsage);

            $this->spacecraftBuildplanRepository->save($plan);

            foreach ($modules as $obj) {
                $mod = $this->buildplanModuleRepository->prototype();
                $mod->setModuleType($obj->getType());
                $mod->setBuildplan($plan);
                $mod->setModule($obj);
                $mod->setModuleSpecial(ModuleSpecialAbilityEnum::getHash($obj->getSpecials()));

                $this->buildplanModuleRepository->save($mod);
            }
        } else {
            $game->addInformationf(
                _('Benutze verfügbaren Bauplan: %s'),
                $plan->getName()
            );
        }

        $queue = $this->colonyShipQueueRepository->prototype();
        $queue->setColony($colony);
        $queue->setUserId($userId);
        $queue->setRump($rump);
        $queue->setSpacecraftBuildplan($plan);
        $queue->setBuildtime($plan->getBuildtime());
        $queue->setFinishDate(time() + $plan->getBuildtime());
        $queue->setBuildingFunction($building_function->getBuildingFunction());
        $queue->setMode(2);
        $queue->setShip($ship);

        $colony->setEps($colony->getEps() - $rump->getEpsCost());

        $this->colonyRepository->save($colony);
        $this->colonyShipQueueRepository->save($queue);

        $ship->setState(SpacecraftStateEnum::RETROFIT);

        $this->shipRepository->save($ship);


        $game->addInformationf(
            _('Die %s wird umgerüstet - Fertigstellung: %s'),
            $ship->getName(),
            date("d.m.Y H:i", (time() + $plan->getBuildtime()))
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
