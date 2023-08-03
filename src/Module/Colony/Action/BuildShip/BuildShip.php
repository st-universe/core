<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildShip;

use request;
use RuntimeException;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\ShipModule\ModuleSpecialAbilityEnum;
use Stu\Module\ShipModule\ModuleTypeDescriptionMapper;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipBuildplan;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class BuildShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BUILD_SHIP';

    private ColonyLoaderInterface $colonyLoader;

    private BuildplanModuleRepositoryInterface $buildplanModuleRepository;

    private ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private ModuleRepositoryInterface $moduleRepository;

    private ColonyShipQueueRepositoryInterface $colonyShipQueueRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ShipCrewCalculatorInterface $shipCrewCalculator;

    private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository;

    private ColonyFunctionManagerInterface $colonyFunctionManager;

    public function __construct(
        ColonyFunctionManagerInterface $colonyFunctionManager,
        ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository,
        ColonyLoaderInterface $colonyLoader,
        BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ModuleRepositoryInterface $moduleRepository,
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyLibFactoryInterface $colonyLibFactory,
        ShipCrewCalculatorInterface $shipCrewCalculator,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->buildplanModuleRepository = $buildplanModuleRepository;
        $this->shipRumpBuildingFunctionRepository = $shipRumpBuildingFunctionRepository;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->moduleRepository = $moduleRepository;
        $this->colonyShipQueueRepository = $colonyShipQueueRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->shipCrewCalculator = $shipCrewCalculator;
        $this->shipRumpModuleLevelRepository = $shipRumpModuleLevelRepository;
        $this->colonyFunctionManager = $colonyFunctionManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $mod = null;
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $userId = $game->getUser()->getId();
        $colonyId = $colony->getId();

        $rump = $this->shipRumpRepository->find(request::indInt('rump'));
        if ($rump === null) {
            return;
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
        $game->setView('SHOW_MODULE_SCREEN');

        if ($this->colonyShipQueueRepository->getAmountByColonyAndBuildingFunction($colonyId, $building_function->getBuildingFunction()) > 0) {
            $game->addInformation(_('In dieser Werft wird bereits ein Schiff gebaut'));
            return;
        }

        if ($colony->getEps() < $rump->getEpsCost()) {
            $game->addInformationf(
                _('Zum Bau wird %d Energie benötigt, es ist jedoch nur %d Energie vorhanden'),
                $rump->getEpsCost(),
                $colony->getEps()
            );
            return;
        }

        if ($colony->isBlocked()) {
            $game->addInformation(_('Schiffbau ist nicht möglich während die Kolonie blockiert wird'));
            return;
        }

        $moduleLevels = $this->shipRumpModuleLevelRepository->getByShipRump($rump->getId());

        $modules = [];
        $sigmod = [];
        $crew_usage = $rump->getBaseCrew();
        for ($i = 1; $i <= ShipModuleTypeEnum::STANDARD_MODULE_TYPE_COUNT; $i++) {
            if ($i !== ShipModuleTypeEnum::MODULE_TYPE_REACTOR) {
                $module = request::postArray('mod_' . $i);
                if (
                    $i != ShipModuleTypeEnum::MODULE_TYPE_SPECIAL
                    && $moduleLevels->{'getModuleMandatory' . $i}() == ShipModuleTypeEnum::MODULE_MANDATORY
                    && count($module) == 0
                ) {
                    $game->addInformationf(
                        _('Es wurde kein Modul des Typs %s ausgewählt'),
                        ModuleTypeDescriptionMapper::getDescription($i)
                    );
                    return;
                }
                if ($i === ShipModuleTypeEnum::MODULE_TYPE_SPECIAL) {
                    $specialCount = 0;
                    foreach ($module as $id) {
                        $specialMod = $this->moduleRepository->find((int) $id);
                        if ($specialMod === null) {
                            throw new RuntimeException(sprintf('moduleId %d does noe exist', $id));
                        }

                        $crew_usage += $specialMod->getCrew();
                        $modules[$id] = $specialMod;
                        $sigmod[$id] = $id;
                        $specialCount++;
                    }

                    if ($specialCount > $rump->getSpecialSlots()) {
                        $game->addInformation(_('Mehr Spezial-Module als der Rumpf gestattet'));
                        return;
                    }
                    continue;
                }
                if (count($module) == 0 || current($module) == 0) {
                    $sigmod[$i] = 0;
                    continue;
                }
                if (current($module) > 0) {
                    /** @var ModuleInterface $mod */
                    $mod = $this->moduleRepository->find((int) current($module));
                    if ($mod->getLevel() > $rump->getModuleLevel()) {
                        $crew_usage += $mod->getCrew() + 1;
                    } else {
                        $crew_usage += $mod->getCrew();
                    }
                } elseif (!$moduleLevels->{'getModuleLevel' . $i}()) {
                    return;
                }
                if ($mod === null) {
                    throw new RuntimeException(sprintf('moduleId %d does not exist', (int)current($module)));
                }
                $modules[current($module)] = $mod;
                $sigmod[$i] = $mod->getId();
            }
        }
        if ($crew_usage > $this->shipCrewCalculator->getMaxCrewCountByRump($rump)) {
            $game->addInformation(_('Crew-Maximum wurde überschritten'));
            return;
        }
        $storage = $colony->getStorage();
        foreach ($modules as $module) {
            if (!$storage->containsKey($module->getCommodityId())) {
                $game->addInformationf(_('Es wird 1 %s benötigt'), $module->getName());
                return;
            }
            $selector = $this->colonyLibFactory->createModuleSelector(
                $module->getType(),
                $colony,
                null,
                $rump,
                $userId
            );
            if (!array_key_exists($module->getId(), $selector->getAvailableModules())) {
                return;
            }
        }
        foreach ($modules as $module) {
            $this->colonyStorageManager->lowerStorage($colony, $module->getCommodity(), 1);
        }
        $game->setView(ShowColony::VIEW_IDENTIFIER);
        $signature = ShipBuildplan::createSignature($sigmod, $crew_usage);
        $plan = $this->shipBuildplanRepository->getByUserShipRumpAndSignature($userId, $rump->getId(), $signature);
        if ($plan === null) {
            $plannameFromRequest = request::indString('buildplanname');
            if (
                $plannameFromRequest !== false
                && $plannameFromRequest !== ''
                && $plannameFromRequest !== 'Bauplanname'
            ) {
                $planname = $plannameFromRequest;
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
            $plan = $this->shipBuildplanRepository->prototype();
            $plan->setUser($game->getUser());
            $plan->setRump($rump);
            $plan->setName($planname);
            $plan->setSignature($signature);
            $plan->setBuildtime($rump->getBuildtime());
            $plan->setCrew($crew_usage);

            $this->shipBuildplanRepository->save($plan);

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
        $queue->setShipBuildplan($plan);
        $queue->setBuildtime($plan->getBuildtime());
        $queue->setFinishDate(time() + $plan->getBuildtime());
        $queue->setBuildingFunctionId($building_function->getBuildingFunction());

        $colony->setEps($colony->getEps() - $rump->getEpsCost());

        $this->colonyRepository->save($colony);
        $this->colonyShipQueueRepository->save($queue);

        $game->addInformationf(
            _('Das Schiff der %s-Klasse wird gebaut - Fertigstellung: %s'),
            $rump->getName(),
            date("d.m.Y H:i", (time() + $plan->getBuildtime()))
        );
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
