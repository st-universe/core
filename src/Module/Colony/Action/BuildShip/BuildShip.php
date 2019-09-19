<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildShip;

use Stu\Lib\ModuleScreen\ModuleSelector;
use Stu\Module\Colony\Lib\ColonyStorageManagerInterface;
use Stu\Module\ShipModule\ModuleTypeDescriptionMapper;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipBuildplan;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ColonyShipQueueRepositoryInterface;
use Stu\Orm\Repository\ModuleRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class BuildShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BUILD_SHIP';

    private $colonyLoader;

    private $buildplanModuleRepository;

    private $shipRumpBuildingFunctionRepository;

    private $shipBuildplanRepository;

    private $moduleRepository;

    private $colonyShipQueueRepository;

    private $shipRumpRepository;

    private $colonyStorageManager;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ModuleRepositoryInterface $moduleRepository,
        ColonyShipQueueRepositoryInterface $colonyShipQueueRepository,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ColonyStorageManagerInterface $colonyStorageManager
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->buildplanModuleRepository = $buildplanModuleRepository;
        $this->shipRumpBuildingFunctionRepository = $shipRumpBuildingFunctionRepository;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->moduleRepository = $moduleRepository;
        $this->colonyShipQueueRepository = $colonyShipQueueRepository;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->colonyStorageManager = $colonyStorageManager;
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
        foreach ($this->shipRumpBuildingFunctionRepository->getByShipRump($rump->getId()) as $bfunc) {
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
        $modules = array();
        $sigmod = array();
        $crewcount = 100;
        for ($i = 1; $i <= MODULE_TYPE_COUNT; $i++) {
            $module = request::postArray('mod_' . $i);
            if ($i != MODULE_TYPE_SPECIAL && $rump->getModuleLevels()->{'getModuleMandatory' . $i}() > 0 && count($module) == 0) {
                $game->addInformationf(
                    _('Es wurde kein Modul des Typs %s ausgewählt'),
                    ModuleTypeDescriptionMapper::getDescription($i)
                );
                return;
            }
            if ($i === MODULE_TYPE_SPECIAL) {
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
            if (!array_key_exists($module->getGoodId(), $storage)) {
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
        // @todo
        $buildtime = 3600;
        $signature = ShipBuildplan::createSignature($sigmod);
        $plan = $this->shipBuildplanRepository->getByUserAndSignature($userId, $signature);
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
            $plan->setUserId($userId);
            $plan->setRump($rump);
            $plan->setName($planname);
            $plan->setSignature($signature);
            $plan->setBuildtime($buildtime);
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
        $queue->setColonyId($colonyId);
        $queue->setUserId($userId);
        $queue->setRump($rump);
        $queue->setShipBuildplan($plan);
        $queue->setBuildtime($buildtime);
        $queue->setFinishDate(time() + $buildtime);
        $queue->setBuildingFunctionId($building_function->getBuildingFunction());

        $this->colonyShipQueueRepository->save($queue);

        $game->addInformationf(
            _('Das Schiff der %s-Klasse wird gebaut - Fertigstellung: %s'),
            $rump->getName(),
            date("d.m.Y H:i", (time() + $buildtime))
        );
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
