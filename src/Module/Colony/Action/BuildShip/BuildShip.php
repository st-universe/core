<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildShip;

use BuildPlanModules;
use ColonyShipQueue;
use ColonyShipQueueData;
use ModuleSelector;
use ModuleType;
use request;
use RumpBuildingFunction;
use ShipBuildplans;
use ShipBuildplansData;
use Shiprump;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class BuildShip implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_BUILD_SHIP';

    private $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $rump = new Shiprump(request::indInt('rump'));

        $building_functions = RumpBuildingFunction::getByRumpId($rump->getId());
        $buildung_function = false;
        foreach ($building_functions as $bfunc) {
            if ($colony->hasActiveBuildingWithFunction($bfunc->getBuildingFunction())) {
                $building_function = $bfunc;
            }
        }
        if (!$building_function) {
            $game->addInformation(_('Die Werft ist nicht aktiviert'));
            return;
        }
        $game->setView('SHOW_MODULE_SCREEN');
        if (ColonyShipQueue::countInstances('WHERE colony_id=' . $colony->getId() . ' AND building_function_id=' . $building_function->getBuildingFunction()) > 0) {
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
                    ModuleType::getDescription($i)
                );
                return;
            }
            if ($i === MODULE_TYPE_SPECIAL) {
                foreach ($module as $key) {
                    $modules[$key] = ResourceCache()->getObject('module', $key);
                    $sigmod[$key] = $modules[$key]->getId();
                }
                continue;
            }
            if (count($module) == 0) {
                continue;
            }
            if (current($module) > 0) {
                $mod = ResourceCache()->getObject('module', current($module));
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
        $storage = &$colony->getStorage();
        foreach ($modules as $module) {
            if (!array_key_exists($module->getGoodId(), $storage)) {
                $game->addInformationf(_('Es wird 1 %s benötigt'), $module->getName());
                return;
            }
            $selector = new ModuleSelector($module->getType(), $colony, $rump,
                currentUser()->getId());
            if (!array_key_exists($module->getId(), $selector->getAvailableModules())) {
                return;
            }
        }
        foreach ($modules as $module) {
            $colony->lowerStorage($module->getGoodId(), 1);
        }
        $game->setView(ShowColony::VIEW_IDENTIFIER);
        // FIXME
        $buildtime = 3600;
        $signature = ShipBuildplansData::createSignature($sigmod);
        $plan = ShipBuildplans::getBySignature(currentUser()->getId(), $signature);
        if (!$plan) {
            $planname = sprintf(
                _('Bauplan %s %s'),
                $rump->getName(),
                date('d.m.Y H:i')
            );
            $game->addInformationf(
                _('Lege neuen Bauplan an: %d'),
                $planname
            );
            $plan = new ShipBuildplansData;
            $plan->setUserId(currentUser()->getId());
            $plan->setRumpId($rump->getId());
            $plan->setName($planname);
            $plan->setSignature($signature);
            $plan->setBuildtime($buildtime);
            $plan->setCrew($crew_usage);
            $plan->setCrewPercentage($crewcount);
            $plan->save();

            BuildPlanModules::insertFromBuildProcess($plan->getId(), $modules);
        } else {
            $game->addInformationf(
                _('Benutze verfügbaren Bauplan: %s'),
                $plan->getName()
            );
        }
        $queue = new ColonyShipQueueData;
        $queue->setColonyId($colony->getId());
        $queue->setUserId(currentUser()->getId());
        $queue->setRumpId($rump->getId());
        $queue->setBuildplanId($plan->getId());
        $queue->setBuildtime($buildtime);
        $queue->setFinishDate(time() + $buildtime);
        $queue->setBuildingFunctionId($building_function->getBuildingFunction());
        $queue->save();

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
