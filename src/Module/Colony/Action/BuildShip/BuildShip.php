<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\BuildShip;

use ColonyShipQueue;
use ColonyShipQueueData;
use Stu\Lib\ModuleScreen\ModuleSelector;
use Stu\Module\ShipModule\ModuleTypeDescriptionMapper;
use request;
use Shiprump;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Entity\ShipBuildplan;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRumpBuildingFunctionRepositoryInterface;

final class BuildShip implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_BUILD_SHIP';

    private $colonyLoader;

    private $buildplanModuleRepository;

    private $shipRumpBuildingFunctionRepository;

    private $shipBuildplanRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        ShipRumpBuildingFunctionRepositoryInterface $shipRumpBuildingFunctionRepository,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->buildplanModuleRepository = $buildplanModuleRepository;
        $this->shipRumpBuildingFunctionRepository = $shipRumpBuildingFunctionRepository;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $userId = $game->getUser()->getId();
        $colonyId = $colony->getId();

        $rump = new Shiprump(request::indInt('rump'));

        $buildung_function = null;
        foreach ($this->shipRumpBuildingFunctionRepository->getByShipRump((int) $rump->getId()) as $bfunc) {
            if ($colony->hasActiveBuildingWithFunction($bfunc->getBuildingFunction())) {
                $building_function = $bfunc;
            }
        }
        if ($building_function === null) {
            $game->addInformation(_('Die Werft ist nicht aktiviert'));
            return;
        }
        $game->setView('SHOW_MODULE_SCREEN');
        if (ColonyShipQueue::countInstances('WHERE colony_id=' . $colonyId . ' AND building_function_id=' . $building_function->getBuildingFunction()) > 0) {
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
            $selector = new ModuleSelector($module->getType(), $colony, $rump, $userId);
            if (!array_key_exists($module->getId(), $selector->getAvailableModules())) {
                return;
            }
        }
        foreach ($modules as $module) {
            $colony->lowerStorage($module->getGoodId(), 1);
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
                _('Lege neuen Bauplan an: %d'),
                $planname
            );
            $plan = $this->shipBuildplanRepository->prototype();
            $plan->setUserId($userId);
            $plan->setRumpId((int) $rump->getId());
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
                $mod->setModuleId((int)$obj->getId());

                $this->buildplanModuleRepository->save($mod);
            }
        } else {
            $game->addInformationf(
                _('Benutze verfügbaren Bauplan: %s'),
                $plan->getName()
            );
        }
        $queue = new ColonyShipQueueData;
        $queue->setColonyId($colonyId);
        $queue->setUserId($userId);
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
