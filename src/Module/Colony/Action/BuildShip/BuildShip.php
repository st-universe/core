<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildShip;

use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Lib\ModuleScreen\ModuleSelector;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\ShipModule\ModuleTypeDescriptionMapper;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipBuildplan;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;
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

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ModuleRepositoryInterface $moduleRepository,
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ColonyStorageManagerInterface $colonyStorageManager,
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
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $userId = $game->getUser()->getId();
        $colonyId = (int) $colony->getId();

        $rump = $this->shipRumpRepository->find((int) request::indInt('rump'));
        if ($rump === null) {
            return;
        }

        $buildung_function = null;
        foreach ($this->shipRumpBuildingFunctionRepository->getByShipRump($rump) as $bfunc) {
            if ($colony->hasActiveBuildingWithFunction($bfunc->getBuildingFunction())) {
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

        $modules = array();
        $sigmod = array();
        $crewcount = 100;
        for ($i = 1; $i <= ShipModuleTypeEnum::MODULE_TYPE_COUNT; $i++) {
            $module = request::postArray('mod_' . $i);
            if ($i != ShipModuleTypeEnum::MODULE_TYPE_SPECIAL && $rump->getModuleLevels()->{'getModuleMandatory' . $i}() > 0 && count($module) == 0) {
                $game->addInformationf(
                    _('Es wurde kein Modul des Typs %s ausgewählt'),
                    ModuleTypeDescriptionMapper::getDescription($i)
                );
                return;
            }
            if ($i === ShipModuleTypeEnum::MODULE_TYPE_SPECIAL) {
                foreach ($module as $key) {
                    /** @var ModuleInterface[] $modules */
                    $modules[$key] = $this->moduleRepository->find((int) $key);
                    $sigmod[$key] = $modules[$key]->getId();
                }
                continue;
            }
            if (count($module) == 0) {
                continue;
            }
            if (current($module) > 0) {
                /** @var ModuleInterface $mod */
                $mod = $this->moduleRepository->find((int) current($module));
                if ($mod->getLevel() > $rump->getModuleLevels()->{'getModuleLevel' . $i}()) {
                    $crewcount += 20;
                } elseif ($mod->getLevel() < $rump->getModuleLevels()->{'getModuleLevel' . $i}()) {
                    $crewcount -= 10;
                }
            } else {
                if (!$rump->getModuleLevels()->{'getModuleLevel' . $i}()) {
                    return;
                }
                $crewcount -= 10;
            }
            $modules[current($module)] = $mod;
            $sigmod[$i] = $mod->getId();
        }
        if ($crewcount > 120) {
            return;
        }
        if ($crewcount == 120) {
            $crew_usage = $rump->getCrew120P();
        } elseif ($crewcount == 110) {
            $crew_usage = $rump->getCrew110P();
        } else {
            $crew_usage = $rump->getCrew100P();
            $crewcount = 100;
        }
        $storage = $colony->getStorage();
        foreach ($modules as $module) {
            if (!$storage->containsKey($module->getGoodId())) {
                $game->addInformationf(_('Es wird 1 %s benötigt'), $module->getName());
                return;
            }
            $selector = new ModuleSelector($module->getType(), $colony, $rump, $userId);
            if (!array_key_exists($module->getId(), $selector->getAvailableModules())) {
                return;
            }
        }
        foreach ($modules as $module) {
            $this->colonyStorageManager->lowerStorage($colony, $module->getCommodity(), 1);
        }
        $game->setView(ShowColony::VIEW_IDENTIFIER);
        $signature = ShipBuildplan::createSignature($sigmod);
        $plan = $this->shipBuildplanRepository->getByUserShipRumpAndSignature($userId, $rump->getId(), $signature);
        if ($plan === null) {
            $planname = sprintf(
                _('Bauplan %s %s'),
                $rump->getName(),
                date('d.m.Y H:i')
            );
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
            $plan->setCrewPercentage($crewcount);

            $this->shipBuildplanRepository->save($plan);

            foreach($modules as $obj) {
                $mod = $this->buildplanModuleRepository->prototype();
                $mod->setModuleType((int) $obj->getType());
                $mod->setBuildplanId((int)$plan->getId());
                $mod->setModule($obj);

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
