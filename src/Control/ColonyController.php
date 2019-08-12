<?php

namespace Stu\Control;

use Building;
use BuildingFieldAlternative;
use BuildingFunctions;
use BuildingUpgrade;
use BuildMenuWrapper;
use BuildplanHangar;
use BuildPlanModules;
use Colfields;
use ColonyMenu;
use ColonyShipQueue;
use ColonyShipQueueData;
use ColonyShipRepair;
use CrewTrainingData;
use Exception;
use FieldTerraforming;
use Good;
use InvalidParamException;
use ModuleBuildingFunction;
use ModuleQueue;
use ModuleScreenTab;
use ModuleScreenTabWrapper;
use ModuleSelector;
use ModuleSelectorSpecial;
use ModuleType;
use ObjectNotFoundException;
use PM;
use request;
use RumpBuildingFunction;
use Ship;
use ShipBuildplans;
use ShipBuildplansData;
use ShipCrew;
use Shiprump;
use Stu\Lib\Session;
use Terraforming;
use TorpedoType;
use Tuple;
use UserColony;

final class ColonyController extends GameController
{

    private $default_tpl = "html/colony.xhtml";

    function __construct(
        Session $session
    )
    {
        parent::__construct($session, $this->default_tpl, "/ Kolonien");
        $this->addNavigationPart(new Tuple("colonylist.php", "Kolonien"));

        $this->getTemplate()->setVar('currentColony', $this->getColony());
        $this->addCallBack("B_SCROLL_BUILDMENU", "scrollBuildMenu");
        $this->addCallBack("B_SWITCH_COLONYMENU", "switchColonyMenu");
        $this->addCallBack("B_BUILD", "buildOnField", true);
        $this->addCallBack("B_ACTIVATE", "activateBuilding", true);
        $this->addCallBack("B_DEACTIVATE", "deActivateBuilding", true);
        $this->addCallBack("B_REPAIR", "repairBuilding", true);
        $this->addCallBack("B_CHANGE_NAME", "changeName");
        $this->addCallBack("B_BEAMTO", "beamTo", true);
        $this->addCallBack("B_BEAMFROM", "beamFrom", true);
        $this->addCallBack("B_REMOVE_BUILDING", "removeBuilding", true);
        $this->addCallBack("B_SET_POPULATIONLIMIT", "setPopulationLimit", true);
        $this->addCallBack("B_ALLOW_IMMIGRATION", "allowImmigration", true);
        $this->addCallBack("B_PERMIT_IMMIGRATION", "permitImmigration", true);
        $this->addCallBack("B_TERRAFORM", "performTerraforming", true);
        $this->addCallBack("B_MULTIPLE_ACTIVATION", "multipleActivation");
        $this->addCallBack("B_MULTIPLE_DEACTIVATION", "multipleDeactivation");
        $this->addCallBack("B_ACTIVATE_EPSRELATED_USER", "activateEpsUsers", true);
        $this->addCallBack("B_DEACTIVATE_EPSRELATED_USER", "deActivateEpsUsers", true);
        $this->addCallBack("B_ACTIVATE_EPSRELATED_PROD", "activateEpsProducers", true);
        $this->addCallBack("B_DEACTIVATE_EPSRELATED_PROD", "deActivateEpsProducers", true);
        $this->addCallBack("B_ACTIVATE_GOODRELATED_USER", "activateGoodUsers");
        $this->addCallBack("B_DEACTIVATE_GOODRELATED_USER", "deActivateGoodUsers");
        $this->addCallBack("B_ACTIVATE_GOODRELATED_PROD", "activateGoodProducers");
        $this->addCallBack("B_DEACTIVATE_GOODRELATED_PROD", "deActivateGoodProducers");
        $this->addCallBack("B_ACTIVATE_BEVRELATED", "activateResidentials", true);
        $this->addCallBack("B_DEACTIVATE_BEVRELATED", "deActivateResidentials", true);
        $this->addCallBack("B_ACTIVATE_WORKRELATED", "activateIndustry", true);
        $this->addCallBack("B_DEACTIVATE_WORKRELATED", "deActivateIndustry", true);
        $this->addCallBack("B_UPGRADE_BUILDING", "upgradeBuilding", true);
        $this->addCallBack('B_MANAGE_ORBITAL_SHIPS', 'manageOrbitalShips', true);
        $this->addCallBack('B_BUILD_SHIP', 'buildShip');
        $this->addCallBack('B_DEL_BUILDPLAN', 'deleteBuildplan');
        $this->addCallBack('B_BUILD_AIRFIELD_RUMP', 'buildAirfieldRump');
        $this->addCallBack('B_BUILD_FIGHTER_SHIPYARD_RUMP', 'buildFighterShipyardRump');
        $this->addCallBack('B_START_AIRFIELD_SHIP', 'startAirfieldShip');
        $this->addCallBack('B_LAND_SHIP', 'landShip', true);
        $this->addCallBack('B_CREATE_MODULES', 'createModules', true);
        $this->addCallBack('B_CANCEL_MODULECREATION', 'cancelModuleCreation');
        $this->addCallBack('B_BUILD_TORPEDOS', 'buildTorpedos');
        $this->addCallBack('B_TRAIN_CREW', 'trainCrew');
        $this->addCallBack('B_REPAIR_SHIP', 'repairShip');

        $this->addView("SHOW_COLONY", "showColony");
        $this->addView("SHOW_BUILDMENU", "showBuildmenu");
        $this->addView("SHOW_BUILDMENU_PART", "showBuildMenuPart");
        $this->addView("SHOW_MANAGEMENT", "showManagement");
        $this->addView("SHOW_MISC", "showMisc");
        $this->addView("SHOW_SOCIAL", "showSocial");
        $this->addView("SHOW_SHIPYARD", "showShipyard");
        $this->addView("SHOW_FIGHTER_SHIPYARD", "showFighterShipyard");
        $this->addView("SHOW_TORPEDO_FAB", "showTorpedoFab");
        $this->addView("SHOW_ACADEMY", "showAcademy");
        $this->addView("SHOW_BUILDPLANS", "showBuildplans");
        $this->addView('SHOW_AIRFIELD', 'showAirfield');
        $this->addView("SHOW_BUILDING_MGMT", "showBuildingManagement");
        $this->addView("SHOW_BUILDING", "showBuilding");
        $this->addView("SHOW_BUILDRESULT", "showBuildResult");
        $this->addView("SHOW_COLONY_SURFACE", "showColonySurface");
        $this->addView("SHOW_FIELD", "showField");
        $this->addView("SHOW_ORBIT_SHIPLIST", "showOrbitShiplist");
        $this->addView("SHOW_BEAMTO", "showBeamTo");
        $this->addView("SHOW_BEAMFROM", "showBeamFrom");
        $this->addView("SHOW_EPSBAR_AJAX", "showEpsBarAjax");
        $this->addView("SHOW_STORAGE_AJAX", "showStorageAjax");
        $this->addView('SHOW_SHIP_VALUES', 'showShipValues');
        $this->addView('SHOW_ORBITAL_SHIPS', 'showOrbitalManagement');
        $this->addView('SHOW_MODULE_SCREEN', 'showModuleScreen');
        $this->addView('SHOW_MODULE_SCREEN_BUILDPLAN', 'showModuleScreenBuildplan');
        $this->addView('SHOW_MODULEFAB', 'showModuleFab');
        $this->addView('SHOW_MODULE_CANCEL', 'showModuleCancel');
        $this->addView('SHOW_SHIP_REPAIR', 'showShipRepair');

        if (!$this->getView()) {
            $this->showColony();
        }
    }

    private $colony = null;

    function showColony()
    {
        $this->addNavigationPart(new Tuple("?id=" . $this->getColony()->getId(),
            $this->getColony()->getNameWithoutMarkup()));
        $this->setPagetitle("Kolonie: " . $this->getColony()->getNameWithoutMarkup());
    }

    protected function showOrbitalManagement()
    {
        $this->addNavigationPart(new Tuple("?id=" . $this->getColony()->getId(),
            $this->getColony()->getNameWithoutMarkup()));
        $this->addNavigationPart(new Tuple("?id=" . $this->getColony()->getId() . '&SHOW_ORBITAL_SHIPS=1',
            _('Orbitalmanagement')));
        $this->setPagetitle("Kolonie: " . $this->getColony()->getNameWithoutMarkup() . _(' / Orbitalmanagement'));
        $this->setTemplateFile('html/orbitalmanagement.xhtml');
    }

    /**
     */
    protected function showShipRepair()
    {
        $this->setFieldByRequest();
        if ($this->getColony()->hasShipyard()) {
            $this->addNavigationPart(new Tuple("?id=" . $this->getColony()->getId(),
                $this->getColony()->getNameWithoutMarkup()));
            $this->addNavigationPart(new Tuple("?id=" . $this->getColony()->getId() . '&SHOW_SHIP_REPAIR=1&fid=' . $this->getField()->getFieldId(),
                _('Schiffreparatur')));
            $this->setPagetitle("Kolonie: " . $this->getColony()->getNameWithoutMarkup() . _(' / Schiffreparatur'));
            $this->setTemplateFile('html/colony_shiprepair.xhtml');
        }
    }

    /**
     */
    public function getShiplistRepairable()
    {
        $ret = array();
        foreach ($this->getColony()->getOrbitShipList() as $fleet) {
            foreach ($fleet['ships'] as $ship_id => $ship) {
                if (!$ship->canBeRepaired() || $ship->getState() == SHIP_STATE_REPAIR) {
                    continue;
                }
                foreach (RumpBuildingFunction::getByRumpId($ship->getRumpId()) as $rump_rel) {
                    if ($this->getField()->getBuilding()->hasFunction($rump_rel->getBuildingFunction())) {
                        $ret[$ship->getId()] = $ship;
                        break;
                    }
                }
            }
        }
        return $ret;
    }

    /**
     */
    protected function showModuleFab()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/cm_modulefab');
    }

    /**
     */
    protected function showModuleCancel()
    {
        $this->getTemplate()->setVar('MODULE', ResourceCache()->getObject('module', request::postIntFatal('module')));
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/queue_count');
    }

    protected function scrollBuildMenu()
    {
        $menu = $this->getGivenBuildMenuRow();
        $offset = $this->getGivenBuildMenuScrollOffset();
        if ($offset < 0) {
            $offset = 0;
        }
        if ($offset % BUILDMENU_SCROLLOFFSET != 0) {
            $offset = floor($offset / BUILDMENU_SCROLLOFFSET);
        }
        $ret = Building::getBuildingMenuList($this->getColony()->getId(), $menu, $offset);
        if (count($ret) == 0) {
            $ret = Building::getBuildingMenuList($this->getColony()->getId(), $menu, $offset - BUILDMENU_SCROLLOFFSET);
            $offset -= BUILDMENU_SCROLLOFFSET;
        }
        $arr['buildings'] = &$ret;
        $this->givenScrollOffset = $offset;
        $this->getTemplate()->setVar('menu', $arr);
        $this->getTemplate()->setVar('menutype', $this->getGivenBuildMenuRow());
        $this->getTemplate()->setVar('scrolloffset', $offset);
        $this->setView("SHOW_BUILDMENU_PART");
    }

    /**
     */
    private function hasSpecialBuilding($function)
    {
        return count(Colfields::getFieldsByBuildingFunction($this->getColony()->getId(), $function)) > 0;
    }


    protected function switchColonyMenu()
    {
        $menu = request::getIntFatal('menu');
        switch ($menu) {
            case MENU_BUILD:
                $this->setView("SHOW_BUILDMENU");
                return;
            case MENU_INFO:
                $this->setView("SHOW_MANAGEMENT");
                return;
            case MENU_OPTION:
                $this->setView("SHOW_MISC");
                return;
            case MENU_SOCIAL:
                $this->setView("SHOW_SOCIAL");
                return;
            case MENU_BUILDINGS:
                $this->setView("SHOW_BUILDING_MGMT");
                return;
            case MENU_SHIPYARD:
                if ($this->getColony()->hasShipyard()) {
                    $this->setView("SHOW_SHIPYARD");
                    $func = new BuildingFunctions(request::getIntFatal('func'));
                    $this->getTemplate()->setVar('FUNC', $func);
                    return;
                }
            case MENU_BUILDPLANS:
                if ($this->getColony()->hasShipyard()) {
                    $this->setView("SHOW_BUILDPLANS");
                    $func = new BuildingFunctions(request::getIntFatal('func'));
                    $this->getTemplate()->setVar('FUNC', $func);
                    return;
                }
            case MENU_AIRFIELD:
                if ($this->getColony()->hasAirfield()) {
                    $this->setView("SHOW_AIRFIELD");
                    return;
                }
            case MENU_MODULEFAB:
                if ($this->getColony()->hasModuleFab()) {
                    $this->setView('SHOW_MODULEFAB');
                    $func = new BuildingFunctions(request::getIntFatal('func'));
                    $this->getTemplate()->setVar('FUNC', $func);
                    return;
                }
            case MENU_FIGHTER_SHIPYARD:
                if ($this->hasSpecialBuilding(BUILDING_FUNCTION_FIGHTER_SHIPYARD)) {
                    $this->setView("SHOW_FIGHTER_SHIPYARD");
                    return;
                }
            case MENU_TORPEDOFAB:
                if ($this->hasSpecialBuilding(BUILDING_FUNCTION_TORPEDO_FAB)) {
                    $this->setView("SHOW_TORPEDO_FAB");
                    return;
                }
            case MENU_ACADEMY:
                if ($this->hasSpecialBuilding(BUILDING_FUNCTION_ACADEMY)) {
                    $this->setView("SHOW_ACADEMY");
                    return;
                }
            default:
                $this->setView("SHOW_MANAGEMENT");
                return;
        }
    }

    private $givenScrollOffset = null;

    function getGivenBuildMenuScrollOffset()
    {
        if ($this->givenScrollOffset === null) {
            $this->givenScrollOffset = request::getInt('offset');
        }
        return $this->givenScrollOffset;
    }

    function getGivenBuildMenuRow()
    {
        return request::getIntFatal('menu');
    }

    function getBuildMenuScrollOffsetStandard()
    {
        return BUILDMENU_SCROLLOFFSET;
    }

    function showBuildmenuPart()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/buildmenu');
    }

    function showBuildmenu()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/cm_buildmenu');
    }

    function showManagement()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/cm_management');
    }

    function showMisc()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/cm_misc');
    }

    protected function showSocial()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/cm_social');
    }

    protected function showShipyard()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/cm_shipyard');
    }

    /**
     */
    protected function showFighterShipyard()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/cm_fighter_shipyard');
    }

    /**
     */
    protected function showTorpedoFab()
    {
        $this->showAjaxMacro('html/colonymacros.xhtml/cm_torpedo_fab');
    }

    /**
     */
    protected function showAcademy()
    {
        $this->showAjaxMacro('html/colonymacros.xhtml/cm_academy');
    }

    /**
     */
    protected function showAirfield()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/cm_airfield');
    }

    protected function showBuildplans()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/cm_buildplans');
    }

    protected function showBuildingManagement()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/cm_building_mgmt');
    }

    function showColonySurface()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/colonysurface');
    }

    function showEpsBarAjax()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/colonyeps');
    }

    function showStorageAjax()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/colonystorage');
    }

    function showOrbitShiplist()
    {
        $this->setPageTitle($this->getColony()->getShipListCount() . " Schiffe im Orbit");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/orbitshiplist');
    }

    function showBuilding()
    {
        $building = new Building(request::getIntFatal('bid'));

        $this->getTemplate()->setVar('buildingdata', $building);
        $this->setPageTitle($building->getName());
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/buildinginfo');
    }

    function getColony()
    {
        if ($this->colony === null) {
            $this->colony = new UserColony(request::indInt('id'));
        }
        return $this->colony;
    }

    private $colfield = null;

    public function getField()
    {
        return $this->colfield;
    }

    private function setFieldByRequest()
    {
        if ($this->colfield === null) {
            if (!request::has('fid')) {
                new InvalidParamException('fid');
            }
            $field_id = (int)request::indInt('fid');
            $this->setField($field_id);
        }
    }

    private function setField($fieldId)
    {
        $this->colfield = Colfields::getByColonyField($fieldId, $this->getColony()->getId());
    }

    function showField()
    {
        $fieldId = request::getIntFatal('fid');
        $this->setFieldByRequest();
        $this->setPageTitle("Feld " . $fieldId . " - Informationen");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/fieldaction');
    }

    private $buildingmenu = null;

    public function getBuildingMenus()
    {
        if ($this->buildingmenu === null) {
            $this->buildingmenu[1]['buildings'] = Building::getBuildingMenuList($this->getColony()->getId(), 1);
            $this->buildingmenu[2]['buildings'] = Building::getBuildingMenuList($this->getColony()->getId(), 2);
            $this->buildingmenu[3]['buildings'] = Building::getBuildingMenuList($this->getColony()->getId(), 3);
        }
        return $this->buildingmenu;
    }

    protected function upgradeBuilding()
    {
        $this->setFieldByRequest();
        $upgrade = new BuildingUpgrade(request::getIntFatal('upid'));
        if ($upgrade->getUpgradeFrom() != $this->getField()->getBuildingId()) {
            return;
        }
        if (!currentUser()->hasResearched($upgrade->getResearchId())) {
            return;
        }
        if ($this->getField()->isInConstruction()) {
            $this->addInformation(_('Das Gebäude auf diesem Feld ist noch nicht fertig'));
            return;
        }
        $storage = $this->getColony()->getStorage();
        foreach ($upgrade->getCost() as $key => $obj) {
            if (!array_key_exists($obj->getGoodId(), $storage)) {
                $this->addInformation(sprintf(_("Es werden %d %s benötigt - Es ist jedoch keines vorhanden"),
                    $obj->getAmount(), getGoodName($obj->getGoodId())));
                return;
            }
            if ($obj->getAmount() > $storage[$obj->getGoodId()]->getCount()) {
                $this->addInformation(sprintf(_("Es werden %d %s benötigt - Vorhanden sind nur %d"), $obj->getAmount(),
                    getGoodName($obj->getGoodId()), $storage[$obj->getGoodId()]->getCount()));
                return;
            }
        }

        if ($this->getColony()->getEps() < $upgrade->getEnergyCost()) {
            $this->addInformation(sprintf(_("Zum Bau wird %d Energie benötigt - Vorhanden ist nur %d"),
                $upgrade->getEnergyCost(), $this->getColony()->getEps()));
            return;
        }


        $this->getColony()->lowerEps($upgrade->getEnergyCost());
        $this->removeBuilding();

        foreach ($upgrade->getCost() as $key => $obj) {
            $this->getColony()->lowerStorage($obj->getGoodId(), $obj->getAmount());
        }
        // Check for alternative building
        $alt_building = BuildingFieldAlternative::getByBuildingField($upgrade->getBuilding()->getId(),
            $this->getField()->getFieldType());
        if ($alt_building) {
            $building = $alt_building->getAlternateBuilding();
        }

        $this->getField()->setBuildingId($upgrade->getBuilding()->getId());
        $this->getField()->setBuildtime($upgrade->getBuilding()->getBuildtime());
        $this->getColony()->save();
        $this->getField()->save();

        $this->addInformation($upgrade->getDescription() . " wird durchgeführt - Fertigstellung: " . $this->getField()->getBuildtimeDisplay(),
            true);
    }

    protected function buildOnField()
    {
        $this->setView("SHOW_BUILDRESULT");
        $this->setFieldByRequest();
        if ($this->getField()->getTerraformingId() > 0) {
            return;
        }
        $building_id = request::indInt('bid');
        $building = new Building($building_id);
        if (!currentUser()->hasResearched($building->getResearchId())) {
            return;
        }
        if (!in_array($this->getField()->getFieldType(), $building->getBuildableFields())) {
            return;
        }
        if ($building->hasLimitColony() && Colfields::countInstances('buildings_id=' . $building->getId() . ' AND colonies_id=' . $this->getColony()->getId()) >= $building->getLimitColony()) {
            $this->addInformation("Dieses Gebäude kann auf dieser Kolonie nur " . $building->getLimitColony() . " mal gebaut werden");
            return;
        }
        if ($building->hasLimit() && Colfields::countInstances('buildings_id=' . $building->getId() . ' AND colonies_id IN (SELECT id FROM stu_colonies WHERE user_id=' . currentUser()->getId() . ')') >= $building->getLimit()) {
            $this->addInformation("Dieses Gebäude kann insgesamt nur " . $building->getLimit() . " mal gebaut werden");
            return;
        }
        $storage = $this->getColony()->getStorage();
        foreach ($building->getCosts() as $key => $obj) {
            if ($this->getField()->hasBuilding()) {
                if (!array_key_exists($key, $storage) && !array_key_exists($key,
                        $this->getField()->getBuilding()->getCosts())) {
                    $this->addInformation("Es werden " . $obj->getCount() . " " . getGoodName($obj->getGoodId()) . " benötigt - Es ist jedoch keines vorhanden");
                    return;
                }
            } else {
                if (!array_key_exists($key, $storage)) {
                    $this->addInformation("Es werden " . $obj->getCount() . " " . getGoodName($obj->getGoodId()) . " benötigt - Es ist jedoch keines vorhanden");
                    return;
                }
            }
            if (!isset($storage[$key])) {
                $amount = 0;
            } else {
                $amount = $storage[$key]->getAmount();
            }
            if ($this->getField()->hasBuilding()) {
                $goods = $this->getField()->getBuilding()->getCosts();
                if (isset($goods[$key])) {
                    $amount += $goods[$key]->getHalfCount();
                }
            }
            if ($obj->getAmount() > $amount) {
                $this->addInformation("Es werden " . $obj->getAmount() . " " . getGoodName($obj->getGoodId()) . " benötigt - Vorhanden sind nur " . $amount);
                return;
            }
        }

        if ($this->getColony()->getEps() < $building->getEpsCost()) {
            $this->addInformation("Zum Bau wird " . $building->getEpsCost() . " Energie benötigt - Vorhanden ist nur " . $this->getColony()->getEps());
            return;
        }

        if ($this->getField()->hasBuilding()) {
            if ($this->getColony()->getEps() > $this->getColony()->getMaxEps() - $this->getField()->getBuilding()->getEpsStorage()) {
                if ($this->getColony()->getMaxEps() - $this->getField()->getBuilding()->getEpsStorage() < $building->getEpsCost()) {
                    $this->addInformation("Nach der Demontage steht nicht mehr genügend Energie zum Bau zur Verfügung");
                    return;
                }
            }
            $this->removeBuilding();
        }

        foreach ($building->getCosts() as $key => $obj) {
            $this->getColony()->lowerStorage($obj->getGoodId(), $obj->getAmount());
        }
        // Check for alternative building
        $alt_building = BuildingFieldAlternative::getByBuildingField($building->getId(),
            $this->getField()->getFieldType());
        if ($alt_building) {
            $building = $alt_building->getAlternateBuilding();
        }

        $this->getColony()->lowerEps($building->getEpsCost());
        $this->getField()->setBuildingId($building->getId());
        $this->getField()->setBuildtime($building->getBuildtime());
        $this->getColony()->save();
        $this->getField()->save();
        $this->addInformation($building->getName() . " wird gebaut - Fertigstellung: " . $this->getField()->getBuildtimeDisplay());
    }

    function showBuildResult()
    {
        $this->addExecuteJS('refreshColony');
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/sitemacros.xhtml/systeminformation');
    }

    protected function activateBuilding()
    {
        $this->setFieldByRequest();
        if (!$this->getField()->hasBuilding()) {
            return;
        }
        if (!$this->getField()->isActivateAble()) {
            return;
        }
        if ($this->getField()->isActive()) {
            return;
        }
        if ($this->getField()->hasHighDamage()) {
            $this->addInformation("Das Gebäude kann aufgrund zu starker Beschädigung nicht aktiviert werden");
            return;
        }
        if ($this->getColony()->getWorkless() < $this->getField()->getBuilding()->getWorkers()) {
            $this->addInformation("Zum aktivieren des Gebäudes werden " . $this->getField()->getBuilding()->getWorkers() . " Arbeiter benötigt");
            return;
        }
        $this->getColony()->lowerWorkless($this->getField()->getBuilding()->getWorkers());
        $this->getColony()->upperWorkers($this->getField()->getBuilding()->getWorkers());
        $this->getColony()->upperMaxBev($this->getField()->getBuilding()->getHousing());
        $this->getField()->setActive(1);
        $this->getField()->save();
        $this->getColony()->save();
        $this->getField()->getBuilding()->postActivation($this->getColony());

        $this->addInformation($this->getField()->getBuilding()->getName() . " auf Feld " . $this->getField()->getFieldId() . " wurde aktiviert");
    }

    protected function deActivateBuilding()
    {
        $this->setFieldByRequest();
        if (!$this->getField()->hasBuilding()) {
            return;
        }
        if (!$this->getField()->isActivateAble()) {
            return;
        }
        if (!$this->getField()->isActive()) {
            return;
        }
        $this->getColony()->upperWorkless($this->getField()->getBuilding()->getWorkers());
        $this->getColony()->lowerWorkers($this->getField()->getBuilding()->getWorkers());
        $this->getColony()->lowerMaxBev($this->getField()->getBuilding()->getHousing());
        $this->getField()->setActive(0);
        $this->getField()->save();
        $this->getColony()->save();
        $this->getField()->getBuilding()->postDeactivation($this->getColony());

        $this->addInformation($this->getField()->getBuilding()->getName() . " auf Feld " . $this->getField()->getFieldId() . " wurde deaktiviert");
    }

    protected function repairBuilding()
    {
        $this->setFieldByRequest();
        if (!$this->getField()->hasBuilding()) {
            return;
        }
        if (!$this->getField()->isDamaged()) {
            return;
        }
        if ($this->getField()->isInConstruction()) {
            return;
        }
        $integrity = round((100 / $this->getField()->getBuilding()->getIntegrity()) * $this->getField()->getIntegrity());
        $eps = round(($this->getField()->getBuilding()->getEpsCost() / 100) * $integrity);
        if ($eps > $this->getColony()->getEps()) {
            $this->addInformation("Zur Reparatur wird " . $eps . " Energie benötigt - Es sind jedoch nur " . $this->getColony()->getEps() . " vorhanden");
            return;
        }

        $cost = $this->getField()->getBuilding()->getCosts();
        foreach ($cost as $key => $obj) {
            $obj->setTempCount(round(($obj->getCount() / 100) * $integrity));
        }
        $ret = calculateCosts($cost, $this->getColony()->getStorage(), $this->getColony());
        if ($ret) {
            $this->addInformation($ret);
            return;
        }

        $this->getColony()->lowerEps($eps);
        $this->getColony()->save();
        $this->getField()->setIntegrity($this->getField()->getBuilding()->getIntegrity());
        $this->getField()->save();

        $this->addInformation($this->getField()->getBuilding()->getName() . " auf Feld " . $this->getField()->getFieldId() . " wurde repariert");
    }

    private $epsbars = null;

    function getEpsBar()
    {
        if ($this->epsbars === null) {
            $width = 360;
            $bars = array();
            if ($this->getColony()->getEpsProduction() < 0) {
                $prod = abs($this->getColony()->getEpsProduction());
                if ($this->getColony()->getEps() - $prod < 0) {
                    $bars[STATUSBAR_RED] = $this->getColony()->getEps();
                    $bars[STATUSBAR_GREY] = $this->getColony()->getMaxEps() - $this->getColony()->getEps();
                } else {
                    $bars[STATUSBAR_YELLOW] = $this->getColony()->getEps() - $prod;
                    $bars[STATUSBAR_RED] = $prod;
                    $bars[STATUSBAR_GREY] = $this->getColony()->getMaxEps() - $this->getColony()->getEps();
                }
            }
            if ($this->getColony()->getEpsProduction() > 0) {
                $prod = $this->getColony()->getEpsProduction();
                if ($this->getColony()->getEps() + $prod > $this->getColony()->getMaxEps()) {
                    $bars[STATUSBAR_YELLOW] = $this->getColony()->getEps();
                    if ($this->getColony()->getEps() < $this->getColony()->getMaxEps()) {
                        $bars[STATUSBAR_GREEN] = $this->getColony()->getMaxEps() - $this->getColony()->getEps();
                    }
                } else {
                    $bars[STATUSBAR_YELLOW] = $this->getColony()->getEps();
                    $bars[STATUSBAR_GREEN] = $prod;
                    $bars[STATUSBAR_GREY] = $this->getColony()->getMaxEps() - $this->getColony()->getEps() - $prod;
                }
            }
            if ($this->getColony()->getEpsProduction() == 0) {
                $bars[STATUSBAR_YELLOW] = $this->getColony()->getEps();
                $bars[STATUSBAR_GREY] = $this->getColony()->getMaxEps() - $this->getColony()->getEps();
            }
            foreach ($bars as $color => $value) {
                if ($this->getColony()->getMaxEps() < $value) {
                    $value = $this->getColony()->getMaxEps();
                }
                if ($value <= 0) {
                    continue;
                }
                $this->epsbars[] = new Tuple($color,
                    round($width / 100 * (100 / $this->getColony()->getMaxEps() * $value)));
            }
        }
        return $this->epsbars;
    }

    /**
     */
    public function hasEffects()
    {
        return count($this->getEffects()) > 0;
    }

    private $effects = null;

    /**
     */
    public function getEffects()
    {
        if ($this->effects === null) {
            $goods = Good::getList('type=' . GOOD_TYPE_EFFECT);
            $prod = $this->getColony()->getProduction();
            $ret = array();
            foreach ($goods as $key => $value) {
                if (!array_key_exists($key, $prod) || $prod[$key]->getProduction() == 0) {
                    continue;
                }
                $ret[$key]['good'] = $value;
                $ret[$key]['production'] = $prod[$key];
            }
            $this->effects = $ret;
        }
        return $this->effects;
    }


    public function getColonyStorageDisplay()
    {
        $goods = Good::getList('type=' . GOOD_TYPE_STANDARD);
        $stor = $this->getColony()->getStorage();
        $prod = $this->getColony()->getProduction();
        $ret = array();
        foreach ($goods as $key => $value) {
            if (array_key_exists($key, $prod)) {
                $ret[$key]['good'] = $value;
                $ret[$key]['production'] = $prod[$key];
                if (!$stor->offsetExists($key)) {
                    $ret[$key]['storage'] = false;
                    $str = 0;
                } else {
                    $str = $stor->offsetGet($key)->getAmount();
                    $ret[$key]['storage'] = $stor->offsetGet($key);
                }
                $proc = $prod[$key]->getProduction();
            } elseif ($stor->offsetExists($key)) {
                $ret[$key]['good'] = $value;
                $ret[$key]['storage'] = $stor->offsetGet($key);
                $ret[$key]['production'] = false;
                $str = $stor->offsetGet($key)->getAmount();
            }
        }
        return $ret;
    }

    function changeName()
    {
        $value = request::postStringFatal('colname');
        $this->getColony()->setName(tidyString($value));
        $this->getColony()->save();
        $this->addInformation("Der Koloniename wurde geändert");
        $this->setSelectedColonyMenu(MENU_OPTION);
    }

    function showBeamTo()
    {
        $target = new Ship(request::getIntFatal('target'));
        $this->preChecks($target);
        $this->setPageTitle("Hochbeamen");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/show_ship_beamto');
        $this->getTemplate()->setVar('targetShip', $target);
    }

    protected function showCrew()
    {
        $target = new Ship(request::getIntFatal('target'));
        $this->preChecks($target);
        $this->setPageTitle("Crew");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/show_ship_crew');
        $this->getTemplate()->setVar('targetShip', $target);
    }

    function showBeamFrom()
    {
        $target = new Ship(request::getIntFatal('target'));
        $this->preChecks($target);
        $this->setPageTitle("Runterbeamen");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/show_ship_beamfrom');
        $this->getTemplate()->setVar('targetShip', $target);
    }

    protected function showCrewSlot()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/crew_slot');

        $target = new Ship(request::getIntFatal('target'));
        currentUser()->enforceOwnerCheck($target);
        $slot = request::getIntFatal('slot');
        $this->getTemplate()->setVar('slot', ShipCrew::getByShipSlot($target->getId(), $slot));
    }

    protected function showShipValues()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/colonymacros.xhtml/shipvalues');

        $target = new Ship(request::getIntFatal('target'));
        currentUser()->enforceOwnerCheck($target);
        $this->getTemplate()->setVar('targetShip', $target);
    }

    function preChecks(&$target)
    {
        if (!checkPosition($this->getColony(),
                $target) || ($target->cloakIsActive() && !$target->ownedByCurrentUser())) {
            new ObjectNotFoundException($target->getId());
        }
        return true;
    }

    protected function beamTo()
    {
        if ($this->getColony()->getEps() == 0) {
            $this->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        $target = new Ship(request::postIntFatal('target'));
        if ($target->shieldIsActive() && $target->getUserId() != currentUser()->getId()) {
            $this->addInformation(sprintf(_('Die %s hat die Schilde aktiviert'), $target->getName()));
            return;
        }
        if (!$target->storagePlaceLeft()) {
            $this->addInformation(sprintf(_('Der Lagerraum der %s ist voll'), $target->getName()));
            return;
        }
        $goods = request::postArray('goods');
        $gcount = request::postArray('count');
        $storage = $this->getColony()->getStorage();
        if ($storage->count() == 0) {
            $this->addInformation(_('Keine Waren zum Beamen vorhanden'));
            return;
        }
        if (count($goods) == 0 || count($gcount) == 0) {
            $this->addInformation(_('Es wurde keine Waren zum Beamen ausgewählt'));
            return;
        }
        $this->addInformation(sprintf(_('Die Kolonie %s hat folgende Waren zur %s transferiert'),
            $this->getColony()->getName(), $target->getName()));
        foreach ($goods as $key => $value) {
            if ($this->getColony()->getEps() < 1) {
                break;
            }
            if (!array_key_exists($key, $gcount) || $gcount[$key] < 1) {
                continue;
            }
            if (!$storage->offsetExists($value)) {
                continue;
            }
            $good = $storage->offsetGet($value);
            $count = $gcount[$key];
            if ($count == "m") {
                $count = $good->getCount();
            } else {
                $count = intval($count);
            }
            if ($count < 1) {
                continue;
            }
            if (!$good->getGood()->isBeamable()) {
                $this->addInformation(sprintf(_("%s ist nicht beambar"), $good->getGood()->getName()));
                continue;
            }
            if ($target->getStorageSum() >= $target->getMaxStorage()) {
                break;
            }
            if ($count > $good->getCount()) {
                $count = $good->getCount();
            }
            if (ceil($count / $good->getGood()->getTransferCount()) > $this->getColony()->getEps()) {
                $count = $this->getColony()->getEps() * $good->getGood()->getTransferCount();
            }
            if ($target->getStorageSum() + $count > $target->getMaxStorage()) {
                $count = $target->getMaxStorage() - $target->getStorageSum();
            }

            $eps_usage = ceil($count / $good->getGood()->getTransferCount());
            $this->addInformation(sprintf(_("%d %s (Energieverbrauch: %d)"), $count, $good->getGood()->getName(),
                $eps_usage));
            $this->getColony()->lowerEps(ceil($count / $good->getGood()->getTransferCount()));
            $target->upperStorage($value, $count);
            $this->getColony()->lowerStorage($value, $count);
            $target->setStorageSum($target->getStorageSum() + $count);
        }
        if ($target->getUserId() != $this->getColony()->getUserId()) {
            $this->sendInformation($target->getUserId(), currentUser()->getId(), PM_SPECIAL_TRADE);
        }
        $this->getColony()->save();
    }

    protected function beamFrom()
    {
        if ($this->getColony()->getEps() == 0) {
            $this->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        $target = new Ship(request::postIntFatal('target'));
        if ($target->shieldIsActive() && $target->getUserId() != currentUser()->getId()) {
            $this->addInformation(sprintf(_('Die %s hat die Schilde aktiviert'), $target->getName()));
            return;
        }
        if (!$this->getColony()->storagePlaceLeft()) {
            $this->addInformation(sprintf(_('Der Lagerraum der %s ist voll'), $this->getColony()->getName()));
            return;
        }
        $goods = request::postArray('goods');
        $gcount = request::postArray('count');
        if ($target->getStorage()->count() == 0) {
            $this->addInformation(_("Keine Waren zum Beamen vorhanden"));
            return;
        }
        if (count($goods) == 0 || count($gcount) == 0) {
            $this->addInformation(_("Es wurde keine Waren zum Beamen ausgewählt"));
            return;
        }
        $this->addInformation(sprintf(_('Die Kolonie %s hat folgende Waren von der %s transferiert'),
            $this->getColony()->getName(), $target->getName()));
        foreach ($goods as $key => $value) {
            if ($this->getColony()->getEps() < 1) {
                break;
            }
            if ($this->getColony()->getStorageSum() >= $this->getColony()->getMaxStorage()) {
                break;
            }
            if (!array_key_exists($key, $gcount) || $gcount[$key] < 1) {
                continue;
            }
            if (!$target->getStorage()->offsetExists($value)) {
                continue;
            }
            $count = $gcount[$key];
            $good = $target->getStorage()->offsetGet($value);
            if (!$good->getGood()->isBeamable()) {
                $this->addInformation(sprintf(_('%s ist nicht beambar')));
                continue;
            }
            if ($count == "m") {
                $count = $good->getCount();
            } else {
                $count = intval($count);
            }
            if ($count < 1) {
                continue;
            }
            if ($count > $good->getCount()) {
                $count = $good->getCount();
            }
            if (ceil($count / $good->getGood()->getTransferCount()) > $this->getColony()->getEps()) {
                $count = $this->getColony()->getEps() * $good->getGood()->getTransferCount();
            }
            if ($this->getColony()->getStorageSum() + $count > $this->getColony()->getMaxStorage()) {
                $count = $this->getColony()->getMaxStorage() - $this->getColony()->getStorageSum();
            }

            $eps_usage = ceil($count / $good->getGood()->getTransferCount());
            $this->addInformation(sprintf(_('%d %s (Energieverbrauch: %d)'), $count, $good->getGood()->getName(),
                $eps_usage));

            $target->lowerStorage($value, $count);
            $this->getColony()->upperStorage($value, $count);
            $this->getColony()->lowerEps(ceil($count / $good->getGood()->getTransferCount()));
            $this->getColony()->setStorageSum($this->getColony()->getStorageSum() + $count);
        }
        if ($target->getUser() != $this->getColony()->getUserId()) {
            $this->sendInformation($target->getUserId(), currentUser()->getId(), PM_SPECIAL_TRADE);
        }
        $this->getColony()->save();
    }

    protected function removeBuilding()
    {
        $this->setFieldByRequest();
        if (!$this->getField()->hasBuilding()) {
            return;
        }
        if (!$this->getField()->getBuilding()->isRemoveAble()) {
            return;
        }
        $this->deActivateBuilding();
        $this->getColony()->lowerMaxStorage($this->getField()->getBuilding()->getStorage());
        $this->getColony()->lowerMaxEps($this->getField()->getBuilding()->getEpsStorage());
        $this->addInformation($this->getField()->getBuilding()->getName() . " auf Feld " . $this->getField()->getFieldId() . " wurde demontiert");
        $this->addInformation("Es konnten folgende Waren recycled werden");
        foreach ($this->getField()->getBuilding()->getCosts() as $key => $value) {
            if ($this->getColony()->getStorageSum() + $value->getHalfCount() > $this->getColony()->getMaxStorage()) {
                $amount = $this->getColony()->getMaxStorage() - $this->getColony()->getStorageSum();
            } else {
                $amount = $value->getHalfCount();
            }
            if ($amount <= 0) {
                break;
            }
            $this->getColony()->upperStorage($value->getGoodId(), $amount);
            $this->addInformation($amount . " " . $value->getGood()->getName());
        }
        $this->getField()->clearBuilding();
        $this->getField()->save();
        $this->getColony()->save();
    }

    function setPopulationLimit()
    {
        $this->setSelectedColonyMenu(MENU_OPTION);
        $limit = request::postIntFatal('poplimit');
        if ($limit == $this->getColony()->getPopulationLimit() || $limit < 0) {
            return;
        }
        $this->getColony()->setPopulationLimit($limit);
        $this->getColony()->save();
        if ($limit > 0) {
            $this->addInformation("Das Bevölkerungslimit wurde auf " . $limit . " gesetzt");
        }
        if ($limit == 0) {
            $this->addInformation("Das Bevölkerungslimit wurde aufgehoben");
        }
    }

    function allowImmigration()
    {
        $this->getColony()->setImmigrationState(1);
        $this->getColony()->save();
        $this->addInformation("Die Einwanderung wurde erlaubt");
        $this->setSelectedColonyMenu(MENU_OPTION);
    }

    function permitImmigration()
    {
        $this->getColony()->setImmigrationState(0);
        $this->getColony()->save();
        $this->addInformation("Die Einwanderung wurde verboten");
        $this->setSelectedColonyMenu(MENU_OPTION);
    }

    function performTerraforming()
    {
        $this->setFieldByRequest();
        if ($this->getField()->getBuildingId() > 0) {
            return;
        }
        if ($this->getField()->getTerraformingId() > 0) {
            return;
        }
        $terraf = new Terraforming(request::getIntFatal('tfid'));
        if ($this->getField()->getFieldType() != $terraf->getSource()) {
            return;
        }
        if (!currentUser()->hasResearched($terraf->getResearchId())) {
            return;
        }
        if ($terraf->getLimit() > 0 && $terraf->getLimit() <= Colfields::countInstances('type=' . $terraf->getDestination())) {
            $this->addInformation('Dieser Feldtyp ist auf diesem Planetentyp nur ' . $terraf->getLimit() . ' mal möglich');
            return;
        }
        if ($terraf->getEpsCost() > $this->getColony()->getEps()) {
            $this->addInformation("Es wird " . $terraf->getEpsCost() . " Energie benötigt - Vorhanden ist nur " . $this->getColony()->getEps());
            return;
        }
        $ret = calculateCosts($terraf->getCosts(), $this->getColony()->getStorage(), $this->getColony());
        if ($ret) {
            $this->addInformation($ret);
            return;
        }
        $this->getColony()->lowerEps($terraf->getEpsCost());
        $time = time() + $terraf->getDuration() + 60;
        FieldTerraforming::addTerraforming($this->getColony()->getId(), $this->getField()->getId(), $terraf->getId(),
            $time);
        $this->getField()->setTerraformingId($terraf->getId());
        $this->getField()->save();
        $this->getColony()->save();
        $this->addInformation($terraf->getDescription() . " wird durchgeführt - Fertigstellung: " . parseDateTime($time));
    }

    public function isColonyMenuSelected()
    {
        return new ColonyMenu($this->selectedColonyMenu);
    }

    public function getBuildingList()
    {
        $list = Colfields::getListBy('colonies_id=' . $this->getColony()->getId() . ' AND buildings_id>0');
        usort($list, 'compareBuildings');
        return $list;
    }

    private function multipleFieldAction($fields, $callback)
    {
        foreach ($fields as $key) {
            $this->setField($key);
            $this->$callback();
        }
    }

    private function multipleFieldObjectAction($fields, $callback)
    {
        foreach ($fields as $key => $obj) {
            $this->setField($obj->getFieldId());
            $this->$callback();
        }
    }

    protected function multipleActivation()
    {
        $list = request::postArrayFatal('selfields');
        $this->multipleFieldAction($list, 'activateBuilding');
        $this->setSelectedColonyMenu(MENU_BUILDINGS);
    }

    protected function multipleDeactivation()
    {
        $list = request::postArrayFatal('selfields');
        $this->multipleFieldAction($list, 'deActivateBuilding');
        $this->setSelectedColonyMenu(MENU_BUILDINGS);
    }

    protected function activateEpsUsers()
    {
        $fields = Colfields::getListBy("colonies_id=" . $this->getColony()->getId() . " AND buildings_id>0 AND aktiv=0 AND buildings_id IN (SELECT id FROM stu_buildings WHERE eps_proc<0)");
        $this->multipleFieldObjectAction($fields, 'activateBuilding');
        $this->setSelectedColonyMenu(MENU_BUILDINGS);
    }

    protected function deActivateEpsUsers()
    {
        $fields = Colfields::getListBy("colonies_id=" . $this->getColony()->getId() . " AND buildings_id>0 AND aktiv=1 AND buildings_id IN (SELECT id FROM stu_buildings WHERE eps_proc<0)");
        $this->multipleFieldObjectAction($fields, 'deActivateBuilding');
        $this->setSelectedColonyMenu(MENU_BUILDINGS);
    }

    protected function activateEpsProducers()
    {
        $fields = Colfields::getListBy("colonies_id=" . $this->getColony()->getId() . " AND buildings_id>0 AND aktiv=0 AND buildings_id IN (SELECT id FROM stu_buildings WHERE eps_proc>0)");
        $this->multipleFieldObjectAction($fields, 'activateBuilding');
        $this->setSelectedColonyMenu(MENU_BUILDINGS);
    }

    protected function deActivateEpsProducers()
    {
        $fields = Colfields::getListBy("colonies_id=" . $this->getColony()->getId() . " AND buildings_id>0 AND aktiv=1 AND buildings_id IN (SELECT id FROM stu_buildings WHERE eps_proc>0)");
        $this->multipleFieldObjectAction($fields, 'deActivateBuilding');
        $this->setSelectedColonyMenu(MENU_BUILDINGS);
    }

    protected function activateGoodUsers()
    {
        $goodId = request::postIntFatal('good');
        $fields = Colfields::getListBy("colonies_id=" . $this->getColony()->getId() . " AND buildings_id>0 AND aktiv=0 AND buildings_id IN (SELECT buildings_id FROM stu_buildings_goods WHERE goods_id=" . $goodId . " AND count<0)");
        $this->multipleFieldObjectAction($fields, 'activateBuilding');
        $this->setSelectedColonyMenu(MENU_BUILDINGS);
    }

    protected function deActivateGoodUsers()
    {
        $goodId = request::postIntFatal('good');
        $fields = Colfields::getListBy("colonies_id=" . $this->getColony()->getId() . " AND buildings_id>0 AND aktiv=1 AND buildings_id IN (SELECT buildings_id FROM stu_buildings_goods WHERE goods_id=" . $goodId . " AND count<0)");
        $this->multipleFieldObjectAction($fields, 'deActivateBuilding');
        $this->setSelectedColonyMenu(MENU_BUILDINGS);
    }

    protected function activateGoodProducers()
    {
        $goodId = request::postIntFatal('good');
        $fields = Colfields::getListBy("colonies_id=" . $this->getColony()->getId() . " AND buildings_id>0 AND aktiv=0 AND buildings_id IN (SELECT buildings_id FROM stu_buildings_goods WHERE goods_id=" . $goodId . " AND count>0)");
        $this->multipleFieldObjectAction($fields, 'activateBuilding');
        $this->setSelectedColonyMenu(MENU_BUILDINGS);
    }

    protected function deActivateGoodProducers()
    {
        $goodId = request::postIntFatal('good');
        $fields = Colfields::getListBy("colonies_id=" . $this->getColony()->getId() . " AND buildings_id>0 AND aktiv=1 AND buildings_id IN (SELECT buildings_id FROM stu_buildings_goods WHERE goods_id=" . $goodId . " AND count>0)");
        $this->multipleFieldObjectAction($fields, 'deActivateBuilding');
        $this->setSelectedColonyMenu(MENU_BUILDINGS);
    }

    protected function activateResidentials()
    {
        $fields = Colfields::getListBy("colonies_id=" . $this->getColony()->getId() . " AND buildings_id>0 AND aktiv=0 AND buildings_id IN (SELECT id FROM stu_buildings WHERE bev_pro>0)");
        $this->multipleFieldObjectAction($fields, 'activateBuilding');
        $this->setSelectedColonyMenu(MENU_BUILDINGS);
    }

    protected function deActivateResidentials()
    {
        $fields = Colfields::getListBy("colonies_id=" . $this->getColony()->getId() . " AND buildings_id>0 AND aktiv=1 AND buildings_id IN (SELECT id FROM stu_buildings WHERE bev_pro>0)");
        $this->multipleFieldObjectAction($fields, 'deActivateBuilding');
        $this->setSelectedColonyMenu(MENU_BUILDINGS);
    }

    protected function activateIndustry()
    {
        $fields = Colfields::getListBy("colonies_id=" . $this->getColony()->getId() . " AND buildings_id>0 AND aktiv=0 AND buildings_id IN (SELECT id FROM stu_buildings WHERE bev_use>0)");
        $this->multipleFieldObjectAction($fields, 'activateBuilding');
        $this->setSelectedColonyMenu(MENU_BUILDINGS);
    }

    protected function deActivateIndustry()
    {
        $fields = Colfields::getListBy("colonies_id=" . $this->getColony()->getId() . " AND buildings_id>0 AND aktiv=1 AND buildings_id IN (SELECT id FROM stu_buildings WHERE bev_use>0)");
        $this->multipleFieldObjectAction($fields, 'deActivateBuilding');
        $this->setSelectedColonyMenu(MENU_BUILDINGS);
    }

    private $useableGoodlist = null;

    public function getUseableGoodlist()
    {
        if ($this->useableGoodlist === null) {
            $this->useableGoodlist = Good::getListByActiveBuildings($this->getColony()->getId());
        }
        return $this->useableGoodlist;
    }

    protected function manageOrbitalShips()
    {
        $ships = request::postArray('ships');
        if (count($ships) == 0) {
            $this->addInformation(_("Es wurden keine Schiffe ausgewählt"));
            return;
        }
        $msg = array();
        $batt = request::postArrayFatal('batt');
        $man = request::postArray('man');
        $unman = request::postArray('unman');
        $wk = request::postArray('wk');
        $torp = request::postArray('torp');
        $torp_type = request::postArray('torp_type');
        $storage = $this->getColony()->getStorage();
        DB()->beginTransaction();
        foreach ($ships as $key => $ship) {
            $shipobj = ResourceCache()->getObject('ship', intval($ship));
            if ($shipobj->cloakIsActive()) {
                continue;
            }
            if (!checkColonyPosition($this->getColony(), $shipobj)) {
                continue;
            }
            if ($shipobj->getIsDestroyed()) {
                continue;
            }
            if ($this->getColony()->getEps() > 0 && $shipobj->getEBatt() < $shipobj->getMaxEbatt() && array_key_exists($ship,
                    $batt)) {
                if ($batt[$ship] == 'm') {
                    $load = $shipobj->getMaxEbatt() - $shipobj->getEBatt();
                } else {
                    $load = intval($batt[$ship]);
                    if ($shipobj->getEBatt() + $load > $shipobj->getMaxEBatt()) {
                        $load = $shipobj->getMaxEBatt() - $shipobj->getEBatt();
                    }
                }
                if ($load > $this->getColony()->getEps()) {
                    $load = $this->getColony()->getEps();
                }
                if ($load > 0) {
                    $shipobj->upperEBatt($load);
                    $this->getColony()->lowerEps($load);
                    $msg[] = sprintf(_('%s: Batterie um %d Einheiten aufgeladen'), $shipobj->getName(), $load);
                    if (!$shipobj->ownedByCurrentUser()) {
                        PM::sendPM(currentUser()->getId(),
                            $shipobj->getUserId(),
                            "Die Kolonie " . $this->getColony()->getName() . " lädt in Sektor " . $this->getColony()->getSectorString() . " die Batterie der " . $shipobj->getName() . " um " . $load . " Einheiten",
                            PM_SPECIAL_TRADE);
                    }
                }
            }
            if (isset($man[$shipobj->getId()]) && $shipobj->currentUserCanMan()) {
                if ($shipobj->getBuildplan()->getCrew() > $shipobj->getUser()->getFreeCrewCount()) {
                    $msg[] = sprintf(_('%s: Nicht genügend Crew vorhanden (%d benötigt)'), $shipobj->getName(),
                        $shipobj->getBuildplan()->getCrew());
                } else {
                    ShipCrew::createByRumpCategory($shipobj);
                    $msg[] = sprintf(_('%s: Die Crew wurde hochgebeamt'), $shipobj->getName());
                    $shipobj->getUser()->setFreeCrewCount($shipobj->getUser()->getFreeCrewCount() - $shipobj->getBuildplan()->getCrew());
                }
            }
            if (isset($unman[$shipobj->getId()]) && $shipobj->currentUserCanUnMan()) {
                ShipCrew::truncate("WHERE ships_id=" . $shipobj->getId());
                $msg[] = sprintf(_('%s: Die Crew wurde runtergebeamt'), $shipobj->getName());
                $shipobj->deactivateSystems();
            }
            if (isset($wk[$shipobj->getId()]) && $wk[$shipobj->getId()] > 0) {
                if ($storage->offsetExists(GOOD_DEUTERIUM) && $storage->offsetExists(GOOD_ANTIMATTER)) {
                    if ($shipobj->getWarpcoreLoad() < $shipobj->getWarpcoreCapacity()) {
                        if ($wk[$shipobj->getId()] == INDICATOR_MAX) {
                            $load = ceil(($shipobj->getWarpcoreCapacity() - $shipobj->getWarpcoreLoad()) / WARPCORE_LOAD);
                        } else {
                            $load = ceil(intval($wk[$shipobj->getId()]) / WARPCORE_LOAD);
                            if ($load * WARPCORE_LOAD > $shipobj->getWarpcoreCapacity() - $shipobj->getWarpcoreLoad()) {
                                $load = ceil(($shipobj->getWarpcoreCapacity() - $shipobj->getWarpcoreLoad()) / WARPCORE_LOAD);
                            }
                        }
                        if ($load >= 1) {
                            if ($storage->offsetGet(GOOD_DEUTERIUM)->getCount() < $load) {
                                $load = $storage->offsetGet(GOOD_DEUTERIUM)->getCount();
                            }
                            if ($storage->offsetGet(GOOD_ANTIMATTER)->getCount() < $load) {
                                $load = $storage->offsetGet(GOOD_ANTIMATTER)->getCount();
                            }
                            $this->getColony()->lowerStorage(GOOD_DEUTERIUM, $load);
                            $this->getColony()->lowerStorage(GOOD_ANTIMATTER, $load);
                            if ($shipobj->getWarpcoreLoad() + $load * WARPCORE_LOAD > $shipobj->getWarpcoreCapacity()) {
                                $load = $shipobj->getWarpcoreCapacity() - $shipobj->getWarpcoreLoad();
                            } else {
                                $load = $load * WARPCORE_LOAD;
                            }
                            $shipobj->upperWarpcoreLoad($load);
                            $msg[] = sprintf(_('Der Warpkern der %s wurde um %d Einheiten aufgeladen'),
                                $shipobj->getName(), $load);
                            if (!$shipobj->ownedByCurrentUser()) {
                                PM::sendPM(currentUser()->getId(),
                                    $shipobj->getUserId(),
                                    sprintf(_('Die Kolonie %s hat in Sektor %s den Warpkern der %s um %d Einheiten aufgeladen'),
                                        $this->getColony()->getName(),
                                        $this->getColony()->getSectorString(),
                                        $shipobj->getName(),
                                        $load),
                                    PM_SPECIAL_TRADE);
                            }
                        }
                    }
                } else {
                    $msg[] = sprintf(_('%s: Es wird Deuterium und Antimaterie zum Aufladen des Warpkerns benötigt'),
                        $shipobj->getName());
                }
            }
            if (isset($torp[$shipobj->getId()]) && $shipobj->canLoadTorpedos()) {
                if ($torp[$shipobj->getId()] == INDICATOR_MAX) {
                    $count = $shipobj->getMaxTorpedos();
                } else {
                    $count = intval($torp[$shipobj->getId()]);
                }
                try {
                    if ($count < 0) {
                        throw new Exception;
                    }
                    if ($count == $shipobj->getTorpedoCount()) {
                        throw new Exception;
                    }
                    if (!$shipobj->ownedByCurrentUser() && $count <= $shipobj->getTorpedoCount()) {
                        throw new Exception;
                    }
                    if ($shipobj->getTorpedoCount() == 0 && (!isset($torp_type[$shipobj->getId()]) || !array_key_exists($torp_type[$shipobj->getId()],
                                $shipobj->getPossibleTorpedoTypes()))) {
                        throw new Exception;
                    }
                    if ($count > $shipobj->getMaxTorpedos()) {
                        $count = $shipobj->getMaxTorpedos();
                    }
                    if ($shipobj->getTorpedoCount() > 0) {
                        $torp_obj = new TorpedoType($shipobj->getTorpedoType());
                        $load = $count - $shipobj->getTorpedoCount();
                        if ($load > 0) {
                            if (!$storage->offsetExists($torp_obj->getGoodId())) {
                                $msg[] = sprintf(_('%s: Es sind keine Torpedos des Typs %s auf der Kolonie vorhanden'),
                                    $shipobj->getName(), $torp_obj->getName());
                                throw new Exception;
                            }
                            if ($load > $storage->offsetGet($torp_obj->getGoodId())->getCount()) {
                                $load = $storage->offsetGet($torp_obj->getGoodId())->getCount();
                            }
                        }
                        $shipobj->setTorpedoCount($shipobj->getTorpedoCount() + $load);
                        if ($load < 0) {
                            $this->getColony()->upperStorage($torp_obj->getGoodId(), abs($load));
                            if ($shipobj->getTorpedoCount() == 0) {
                                $shipobj->setTorpedoType(0);
                                $shipobj->setTorpedos(0);
                            }
                            $msg[] = sprintf(_('%s: Es wurden %d Torpedos des Typs %s vom Schiff transferiert'),
                                $shipobj->getName(), abs($load), $torp_obj->getName());
                        } elseif ($load > 0) {
                            $this->getColony()->lowerStorage($torp_obj->getGoodId(), $load);
                            $msg[] = sprintf(_('%s: Es wurden %d Torpedos des Typs %s zum Schiff transferiert'),
                                $shipobj->getName(), $load, $torp_obj->getName());
                        }
                    } else {
                        $type = intval($torp_type[$shipobj->getId()]);
                        $torp_obj = new TorpedoType($type);
                        if (!$storage->offsetExists($torp_obj->getGoodId())) {
                            throw new Exception;
                        }
                        if ($count > $storage->offsetGet($torp_obj->getGoodId())->getCount()) {
                            $count = $storage->offsetGet($torp_obj->getGoodId())->getCount();
                        }
                        if ($count > $shipobj->getMaxTorpedos()) {
                            $count = $shipobj->getMaxTorpedos();
                        }
                        $shipobj->setTorpedoType($type);
                        $shipobj->setTorpedoCount($count);
                        $this->getColony()->lowerStorage($torp_obj->getGoodId(), $count);
                        $msg[] = sprintf(_('%s: Es wurden %d Torpedos des Typs %s zum Schiff transferiert'),
                            $shipobj->getName(), $count, $torp_obj->getName());
                    }
                } catch (Exception $e) {
                    // nothing
                }
            }
            $shipobj->save();
        }
        $this->getColony()->save();
        DB()->commitTransaction();
        $this->addInformationMerge($msg);
    }

    private $rumplist = null;

    /**
     */
    public function getBuildableRumps()
    {
        if ($this->rumplist === null) {
            $this->rumplist = Shiprump::getBuildableRumpsByUser(currentUser()->getId());
        }
        return $this->rumplist;
    }

    private $rumplist_function = null;

    /**
     */
    public function getBuildableRumpsByBuildingFunction()
    {
        if ($this->rumplist_function === null) {
            $func_id = request::getIntFatal('func');
            $function = new BuildingFunctions($func_id);
            $this->rumplist_function = Shiprump::getBuildableRumpsByBuildingFunction(currentUser()->getId(),
                $function->getFunction());
        }
        return $this->rumplist_function;
    }

    private $selected_buildingfunction;

    /**
     */
    public function getSelectedBuildingFunction()
    {
        if ($this->selected_buildingfunction === null) {
            $this->selected_buildingfunction = new BuildingFunctions(request::getIntFatal('func'));
        }
        return $this->selected_buildingfunction;
    }


    private $buildplanList = null;

    /**
     */
    public function getBuildplans()
    {
        if ($this->buildplanList === null) {
            $this->buildplanList = ShipBuildplans::getBuildplansByUserAndFunction(currentUser()->getId(),
                $this->getSelectedBuildingFunction()->getFunction());
        }
        return $this->buildplanList;
    }

    /**
     */
    private function showModuleSelectScreen()
    {
        $this->setPagetitle("Kolonie: " . $this->getColony()->getNameWithoutMarkup() . _(' / Schiffbau'));
        $this->setTemplateFile('html/modulescreen.xhtml');
        $this->getSelectedRump()->enforceBuildableByUser(currentUser()->getId());
    }


    protected function showModuleScreen()
    {
        $this->addNavigationPart(new Tuple("?id=" . $this->getColony()->getId(),
            $this->getColony()->getNameWithoutMarkup()));
        $this->addNavigationPart(new Tuple("?id=" . $this->getColony()->getId() . '&SHOW_MODULE_SCREEN=1&rump=' . $this->getSelectedRump()->getId(),
            _('Schiffbau')));
        $this->showModuleSelectScreen();
    }

    /**
     */
    protected function showModuleScreenBuildplan()
    {
        $planId = request::getIntFatal('planid');
        $plan = new ShipBuildplans($planId);
        if (!$plan->ownedByCurrentUser()) {
            return;
        }
        $this->loadModuleScreenTabs($plan);
        $this->selectedRump = $plan->getRump();
        $this->selectedBuildplan = $plan;
        $this->addNavigationPart(new Tuple("?id=" . $this->getColony()->getId(),
            $this->getColony()->getNameWithoutMarkup()));
        $this->addNavigationPart(new Tuple("?id=" . $this->getColony()->getId() . '&SHOW_MODULE_SCREEN_BUILDPLAN=1&planid=' . $this->getSelectedBuildplan()->getId(),
            _('Schiffbau nach Bauplan') . ': ' . $this->getSelectedBuildplan()->getName()));
        $this->showModuleSelectScreen();
    }

    private $selectedRump = null;

    /**
     */
    public function getSelectedRump()
    {
        if ($this->selectedRump === null) {
            $this->selectedRump = new Shiprump(request::indInt('rump'));
        }
        return $this->selectedRump;
    }

    private $selectedBuildplan = false;

    /**
     */
    public function getSelectedBuildplan()
    {
        return $this->selectedBuildplan;
    }

    /**
     */
    private function loadModuleScreenTabs($buildplan = false)
    {
        $tabs = new ModuleScreenTabWrapper;
        for ($i = 1; $i <= MODULE_TYPE_COUNT; $i++) {
            if ($buildplan) {
                $this->selectedRump = $buildplan->getRump();
            }
            $tabs->register(new ModuleScreenTab($i, $this->getColony(), $this->getSelectedRump(), $buildplan));
        }
        $this->moduleScreenTabs = $tabs;
    }

    private $moduleScreenTabs = null;

    /**
     */
    public function getModuleScreenTabs()
    {
        if ($this->moduleScreenTabs === null) {
            $this->loadModuleScreenTabs();
        }
        return $this->moduleScreenTabs;
    }

    /**
     */
    public function getModuleSelectors()
    {
        $retv = array();
        for ($i = 1; $i <= MODULE_TYPE_COUNT; $i++) {
            if ($i == MODULE_TYPE_SPECIAL) {
                $retv[] = new ModuleSelectorSpecial($i, $this->getColony(), $this->getSelectedRump(),
                    currentUser()->getId(), $this->getSelectedBuildplan());
            } else {
                $retv[] = new ModuleSelector($i, $this->getColony(), $this->getSelectedRump(), currentUser()->getId(),
                    $this->getSelectedBuildplan());
            }
        }
        return $retv;
    }

    /**
     */
    protected function buildShip()
    {
        $building_functions = RumpBuildingFunction::getByRumpId($this->getSelectedRump()->getId());
        $buildung_function = false;
        foreach ($building_functions as $bfunc) {
            if ($this->getColony()->hasActiveBuildingWithFunction($bfunc->getBuildingFunction())) {
                $building_function = $bfunc;
            }
        }
        if (!$building_function) {
            $this->addInformation(_('Die Werft ist nicht aktiviert'));
            return;
        }
        $this->setView('SHOW_MODULE_SCREEN');
        if (ColonyShipQueue::countInstances('WHERE colony_id=' . $this->getColony()->getId() . ' AND building_function_id=' . $building_function->getBuildingFunction()) > 0) {
            $this->addInformation(_('In dieser Werft wird bereits ein Schiff gebaut'));
            return;
        }
        $modules = array();
        $sigmod = array();
        $crewcount = 100;
        for ($i = 1; $i <= MODULE_TYPE_COUNT; $i++) {
            $module = request::postArray('mod_' . $i);
            if ($i != MODULE_TYPE_SPECIAL && $this->getSelectedRump()->getModuleLevels()->{'getModuleMandatory' . $i}() > 0 && count($module) == 0) {
                $this->addInformation(sprintf(_('Es wurde kein Modul des Typs %s ausgewählt'),
                    ModuleType::getDescription($i)));
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
                if ($mod->getLevel() > $this->getSelectedRump()->getModuleLevels()->{'getModuleLevel' . $i}()) {
                    $crewcount += 20;
                } elseif ($mod->getLevel() < $this->getSelectedRump()->getModuleLevels()->{'getModuleLevel' . $i}()) {
                    $crewcount -= 10;
                }
            } else {
                if (!$this->getSelectedRump()->getModuleLevels()->{'getModuleLevel' . $i}()) {
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
            $crew_usage = $this->getSelectedRump()->getCrew120P();
        } elseif ($crewcount == 110) {
            $crew_usage = $this->getSelectedRump()->getCrew110P();
        } else {
            $crew_usage = $this->getSelectedRump()->getCrew100P();
            $crewcount = 100;
        }
        $storage = &$this->getColony()->getStorage();
        foreach ($modules as $module) {
            if (!array_key_exists($module->getGoodId(), $storage)) {
                $this->addInformation(sprintf(_('Es wird 1 %s benötigt'), $module->getName()));
                return;
            }
            $selector = new ModuleSelector($module->getType(), $this->getColony(), $this->getSelectedRump(),
                currentUser()->getId());
            if (!array_key_exists($module->getId(), $selector->getAvailableModules())) {
                return;
            }
        }
        DB()->beginTransaction();
        foreach ($modules as $module) {
            $this->getColony()->lowerStorage($module->getGoodId(), 1);
        }
        $this->setView("SHOW_COLONY");
        // FIXME
        $buildtime = 3600;
        $signature = ShipBuildplansData::createSignature($sigmod);
        $plan = ShipBuildplans::getBySignature(currentUser()->getId(), $signature);
        if (!$plan) {
            $planname = _('Bauplan ') . $this->getSelectedRump()->getName() . ' ' . date("d.m.Y H:i");
            $this->addInformation(_("Lege neuen Bauplan an: ") . $planname);
            $plan = new ShipBuildplansData;
            $plan->setUserId(currentUser()->getId());
            $plan->setRumpId($this->getSelectedRump()->getId());
            $plan->setName($planname);
            $plan->setSignature($signature);
            $plan->setBuildtime($buildtime);
            $plan->setCrew($crew_usage);
            $plan->setCrewPercentage($crewcount);
            $plan->save();

            BuildPlanModules::insertFromBuildProcess($plan->getId(), $modules);
        } else {
            $this->addInformation(_("Benutze verfügbaren Bauplan: ") . $plan->getName());
        }
        $queue = new ColonyShipQueueData;
        $queue->setColonyId($this->getColony()->getId());
        $queue->setUserId(currentUser()->getId());
        $queue->setRumpId($this->getSelectedRump()->getId());
        $queue->setBuildplanId($plan->getId());
        $queue->setBuildtime($buildtime);
        $queue->setFinishDate(time() + $buildtime);
        $queue->setBuildingFunctionId($building_function->getBuildingFunction());
        $queue->save();
        DB()->commitTransaction();

        $this->addInformation(sprintf(_('Das Schiff der %s-Klasse wird gebaut - Fertigstellung: %s'),
            $this->getSelectedRump()->getName(), date("d.m.Y H:i", (time() + $buildtime))));
    }

    private $shipqueue = null;

    /**
     */
    public function getShipBuildProgress()
    {
        if ($this->shipqueue === null) {
            $this->shipqueue = ColonyShipQueue::getByColonyId($this->getColony()->getId());
        }
        return $this->shipqueue;
    }

    private $ship_repair_progress;

    /**
     */
    public function getShipRepairProgress()
    {
        if ($this->ship_repair_progress === null) {
            $this->ship_repair_progress = ColonyShipRepair::getByColonyField($this->getColony()->getId(),
                $this->getField()->getFieldId());
        }
        return $this->ship_repair_progress;
    }

    /**
     */
    protected function deleteBuildplan()
    {
        $planid = request::getIntFatal('planid');
        $plan = new ShipBuildplans($planid);
        $plan->delete();

        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->getTemplate()->setRef('currentColony', $this->getColony());
        $this->getTemplate()->setVar('FUNC', $this->getSelectedBuildingFunction());
        $this->setAjaxMacro('html/colonymacros.xhtml/cm_buildplans');
    }

    /**
     */
    public function getModuleSlots()
    {
        return range(1, MODULE_TYPE_COUNT);
    }

    /**
     */
    public function getBuildMenu()
    {
        return new BuildMenuWrapper;
    }

    private $selectedColonyMenu = null;

    /**
     */
    public function getSelectedColonyMenu()
    {
        switch ($this->selectedColonyMenu) {
            case MENU_OPTION:
                return 'cm_misc';
            case MENU_BUILD:
                return 'cm_buildmenu';
            case MENU_SOCIAL:
                return 'cm_social';
            case MENU_BUILDINGS:
                return 'cm_building_mgmt';
            case MENU_AIRFIELD:
                return 'cm_airfield';
            case MENU_MODULEFAB:
                return 'cm_modulefab';
            default:
                return 'cm_management';
        }
    }

    /**
     */
    public function setSelectedColonyMenu($value)
    {
        $this->selectedColonyMenu = $value;
    }

    private $available_airfield_rumps = null;

    /**
     */
    public function getAvailableAirfieldRumps()
    {
        if ($this->available_airfield_rumps === null) {
            $this->available_airfield_rumps = Shiprump::getBuildableRumpsByBuildingFunction(currentUser()->getId(),
                BUILDING_FUNCTION_AIRFIELD);
        }
        return $this->available_airfield_rumps;
    }

    private $available_fighter_shipyard_rumps = null;

    /**
     */
    public function getAvailableFighterShipyardRumps()
    {
        if ($this->available_fighter_shipyard_rumps === null) {
            $this->available_fighter_shipyard_rumps = Shiprump::getBuildableRumpsByBuildingFunction(currentUser()->getId(),
                BUILDING_FUNCTION_FIGHTER_SHIPYARD);
        }
        return $this->available_fighter_shipyard_rumps;
    }

    private $startable_airfield_rumps = null;

    /**
     */
    public function getStartableAirfieldRumps()
    {
        if ($this->startable_airfield_rumps === null) {
            $this->startable_airfield_rumps = Shiprump::getBy('WHERE id IN (SELECT rump_id FROM stu_rumps_user WHERE user_id=' . currentUser()->getId() . ')
					AND good_id IN (SELECT goods_id FROM stu_colonies_storage WHERE colonies_id=' . $this->getColony()->getId() . ') GROUP BY id');
        }
        return $this->startable_airfield_rumps;
    }

    /**
     */
    protected function buildAirfieldRump()
    {
        $rump_id = request::postInt('buildrump');
        if (!array_key_exists($rump_id, $this->getAvailableAirfieldRumps())) {
            return;
        }
        $this->buildShipRump($rump_id);
    }

    /**
     */
    protected function buildFighterShipyardRump()
    {
        $rump_id = request::postInt('buildrump');
        if (!array_key_exists($rump_id, $this->getAvailableFighterShipyardRumps())) {
            return;
        }
        $this->buildShipRump($rump_id);
    }

    /**
     */
    private function buildShipRump($rump_id)
    {
        $rump = ResourceCache()->getObject('rump', $rump_id);
        DB()->beginTransaction();
        if ($rump->getEpsCost() > $this->getColony()->getEps()) {
            $this->addInformation(sprintf(_('Es wird %d Energie benötigt - Vorhanden ist nur %d'), $rump->getEpsCost(),
                $this->getColony()->getEps()));
            return;
        }
        $storage = &$this->getColony()->getStorage();
        foreach ($rump->getBuildingCosts() as $key => $cost) {
            if (!array_key_exists($cost->getGoodId(), $storage)) {
                $this->addInformation(sprintf(_('Es wird %d %s benötigt'), $cost->getAmount(),
                    $cost->getGood()->getName()));
                $this->rollbackTransaction();
                return;
            }
            if ($storage[$cost->getGoodId()]->getAmount() < $cost->getAmount()) {
                $this->addInformation(sprintf(_('Es wird %d %s benötigt - Vorhanden ist nur %d'), $cost->getAmount(),
                    $cost->getGood()->getName(), $storage[$cost->getGoodId()]->getAmount()));
                $this->rollbackTransaction();
                return;
            }
            $this->getColony()->lowerStorage($cost->getGoodId(), $cost->getAmount());
        }
        $this->getColony()->lowerEps($rump->getEpsCost());
        $this->getColony()->upperStorage($rump->getGoodId(), 1);
        $this->getColony()->save();
        DB()->commitTransaction();
        $this->addInformation(sprintf(_('%s-Klasse wurde gebaut'), $rump->getName()));
    }

    /**
     */
    protected function startAirfieldShip()
    {
        if (!isAdmin(currentUser()->getId())) {
            $this->addInformation('Geht grad ned ... aber bald!');
            return;
        }
        $rump_id = request::postInt('startrump');
        if (!array_key_exists($rump_id, $this->getStartableAirfieldRumps())) {
            return;
        }
        $rump = ResourceCache()->getObject('rump', $rump_id);
        if ($rump->canColonize() && Ship::countInstances('WHERE user_id=' . currentUser()->getId() . ' AND rumps_id IN (SELECT rumps_id FROM stu_rumps_specials WHERE special=' . RUMP_SPECIAL_COLONIZE . ')') > 0) {
            $this->addInformation(_("Es kann nur ein Schiff mit Kolonisierungsfunktion genutzt werden"));
            return;
        }
        $hangar = BuildplanHangar::getBy('WHERE rump_id=' . $rump_id);

        if ($hangar->getBuildplan()->getCrew() > currentUser()->getFreeCrewCount()) {
            $this->addInformation(_("Es ist für den Start des Schiffes nicht genügend Crew vorhanden"));
            return;
        }
        if (Ship::countInstances('WHERE user_id=' . currentUser()->getId()) >= 10) {
            $this->addInformation(_("Im Moment sind nur 10 Schiffe pro Siedler erlaubt"));
            return;
        }
        DB()->beginTransaction();
        // XXX starting costs
        if ($this->getColony()->getEps() < 10) {
            $this->addInformation(sprintf(_('Es wird %d Energie benötigt - Vorhanden ist nur %d'), 10,
                $this->getColony()->getEps()));
            return;
        }
        $storage = &$this->getColony()->getStorage();
        if (!array_key_exists($rump->getGoodId(), $storage)) {
            $this->addInformation(sprintf(_('Es wird %d %s benötigt'), 1, getGoodName($rump->getGoodId())));
            $this->rollbackTransaction();
            return;
        }

        $ship = Ship::createBy(currentUser()->getId(), $rump_id, $hangar->getBuildplanId(), $this->getColony());

        ShipCrew::createByRumpCategory($ship);

        if ($hangar->getDefaultTorpedoTypeId()) {
            $torp = new TorpedoType($hangar->getDefaultTorpedoTypeId());
            if ($this->getColony()->getStorage()->offsetExists($torp->getGoodId())) {
                $count = $ship->getMaxTorpedos();
                if ($count > $this->getColony()->getStorage()->offsetGet($torp->getGoodId())->getCount()) {
                    $count = $this->getColony()->getStorage()->offsetGet($torp->getGoodId())->getCount();
                }
                $ship->setTorpedoType($torp->getId());
                $ship->setTorpedoCount($count);
                $ship->save();
                $this->getColony()->lowerStorage($torp->getGoodId(), $count);
            }
        }
        if ($rump->canColonize()) {
            $ship->setEps($ship->getMaxEps());
            $ship->setWarpcoreLoad($ship->getWarpcoreCapacity());
            $ship->save();
        }
        $this->getColony()->lowerEps(10);
        $this->getColony()->lowerStorage($rump->getGoodId(), 1);
        $this->getColony()->save();

        if ($this->getColony()->getSystem()->getDatabaseId()) {
            $this->checkDatabaseItem($this->getColony()->getSystem()->getDatabaseId());
        }
        if ($this->getColony()->getSystem()->getSystemType()->getDatabaseId()) {
            $this->checkDatabaseItem($this->getColony()->getSystem()->getSystemType()->getDatabaseId());
        }
        if ($rump->getDatabaseId()) {
            $this->checkDatabaseItem($rump->getDatabaseId());
        }
        $this->addInformation(_('Das Schiff wurde gestartet'));
        DB()->commitTransaction();
    }

    /**
     */
    private function rollbackTransaction()
    {
        DB()->rollbackTransaction();
        $this->getColony()->clearCache();
        $this->colony = null;
    }

    /**
     */
    protected function landShip()
    {
        $ship = ResourceCache()->getObject('ship', request::getIntFatal('shipid'));
        if (!$ship->ownedByCurrentUser() || !$ship->canLandOnCurrentColony()) {
            return;
        }
        if (!$this->getColony()->storagePlaceLeft()) {
            $this->addInformation(_('Kein Lagerraum verfügbar'));
            return;
        }
        DB()->beginTransaction();
        $this->getColony()->upperStorage($ship->getRump()->getGoodId(), 1);
        $this->getColony()->setStorageSum($this->getColony()->getStorageSum() + 1);
        foreach ($ship->getStorage() as $key => $stor) {
            $stor->getCount() + $this->getColony()->getStorageSum() > $this->getColony()->getMaxStorage() ? $count = $this->getColony()->getMaxStorage() - $this->getColony()->getStorageSum() : $count = $stor->getCount();
            if ($count > 0) {
                $this->getColony()->upperStorage($stor->getGoodId(), $count);
                $this->getColony()->setStorageSum($this->getColony()->getStorageSum() + $count);
            }
        }
        $this->getColony()->save();
        $this->addInformation(sprintf(_('Die %s ist gelandet'), $ship->getName()));
        $ship->remove();
        DB()->commitTransaction();
    }

    /**
     */
    protected function createModules()
    {
        $modules = request::postArrayFatal('module');
        $func = request::postIntFatal('func');
        if (count(Colfields::getFieldsByBuildingFunction($this->getColony()->getId(), $func)) == 0) {
            return;
        }
        $prod = array();
        $modules_av = ModuleBuildingFunction::getByFunctionAndUser($func, currentUser()->getId());
        $storage = $this->getColony()->getStorage();
        foreach ($modules as $module_id => $count) {
            if (!array_key_exists($module_id, $modules_av)) {
                continue;
            }
            $count = intval($count);
            $module = $modules_av[$module_id]->getModule();
            if ($module->getEcost() * $count > $this->getColony()->getEps()) {
                $count = floor($this->getColony()->getEps() / $module->getEcost());
            }
            if ($count == 0) {
                continue;
            }
            try {
                foreach ($module->getCost() as $cid => $cost) {
                    if (!array_key_exists($cost->getGoodId(), $storage)) {
                        $prod[] = sprintf(_("Zur Herstellung von %s wird %s benötigt"), $module->getName(),
                            $cost->getGood()->getName());
                        throw new Exception;
                    }
                    if ($storage[$cost->getGoodId()]->getCount() < $cost->getCount()) {
                        $prod[] = sprintf(_("Zur Herstellung von %s wird %d %s benötigt"), $module->getName(),
                            $cost->getCount(), $cost->getGood()->getName());
                        throw new Exception;
                    }
                    if ($storage[$cost->getGoodId()]->getCount() < $cost->getCount() * $count) {
                        $count = floor($storage[$cost->getGoodId()]->getCount() / $cost->getCount());
                    }
                }
            } catch (Exception $e) {
                continue;
            }
            DB()->beginTransaction();
            foreach ($module->getCost() as $cid => $cost) {
                $this->getColony()->lowerStorage($cost->getGoodId(), $cost->getCount() * $count);
            }
            $this->getColony()->lowerEps($count * $module->getEcost());
            $this->getColony()->save();
            ModuleQueue::queueModule($this->getColony()->getId(), $func, $module_id, $count);
            DB()->commitTransaction();
            $prod[] = $count . ' ' . $module->getName();
        }
        if (count($prod) == 0) {
            $this->addInformation(_('Es wurden keine Module hergestellt'));
            return;
        }
        $this->addInformation(_('Es wurden folgende Module zur Warteschlange hinzugefügt'));
        foreach ($prod as $msg) {
            $this->addInformation($msg);
        }
    }

    /**
     */
    protected function cancelModuleCreation()
    {
        $this->setView('SHOW_MODULE_CANCEL');
        $module_id = request::postIntFatal('module');
        $function = request::postIntFatal('func');
        $count = request::postInt('count');
        $module = ResourceCache()->getObject('module', $module_id);
        if (count(Colfields::getFieldsByBuildingFunction($this->getColony()->getId(), $function)) == 0) {
            return;
        }
        if ($count == 0) {
            return;
        }
        $queue = ModuleQueue::getBy('WHERE colony_id=' . $this->getColony()->getId() . ' AND module_id=' . $module_id . ' AND buildingfunction=' . $function);
        if (!$queue) {
            return false;
        }
        if ($queue->getCount() < $count) {
            $count = $queue->getCount();
        }
        DB()->beginTransaction();
        if ($count >= $queue->getCount()) {
            $queue->deleteFromDatabase();
        } else {
            $queue->setCount($queue->getCount() - $count);
            $queue->save();
        }
        if ($module->getEcost() * $count > $this->getColony()->getMaxEps() - $this->getColony()->getEps()) {
            $this->getColony()->setEps($this->getColony()->getMaxEps());
        } else {
            $this->getColony()->upperEps($count * $module->getEcost());
        }
        foreach ($module->getCost() as $cid => $cost) {
            if ($this->getColony()->getStorageSum() >= $this->getColony()->getMaxStorage()) {
                break;
            }
            if ($cost->getCount() * $count > $this->getColony()->getMaxStorage() - $this->getColony()->getStorageSum()) {
                $gc = $this->getColony()->getMaxStorage() - $this->getColony()->getStorageSum();
            } else {
                $gc = $count * $cost->getCount();
            }
            $this->getColony()->upperStorage($cost->getGoodId(), $gc);
            $this->getColony()->setStorageSum($this->getColony()->getStorageSum() + $gc);
        }
        $this->getColony()->save();
        DB()->commitTransaction();
    }

    /**
     */
    public function getBuildableTorpedoTypes()
    {
        if ($this->buildable_torpedo_types === null) {
            $this->buildable_torpedo_types = TorpedoType::getBuildableTorpedoTypesByUser(currentUser()->getId());
        }
        return $this->buildable_torpedo_types;
    }

    /**
     */
    protected function buildTorpedos()
    {
        $torps = request::postArray('torps');
        $torpedo_types = $this->getBuildableTorpedoTypes();
        $storage = $this->getColony()->getStorage();
        $msg = array();
        DB()->beginTransaction();
        foreach ($torps as $torp_id => $count) {
            if (!array_key_exists($torp_id, $torpedo_types)) {
                continue;
            }
            $count = intval($count);
            $torp = $torpedo_types[$torp_id];
            if ($torp->getECost() * $count > $this->getColony()->getEps()) {
                $count = floor($this->getColony()->getEps() / $torp->getEcost());
            }
            if ($count <= 0) {
                continue;
            }
            foreach ($torp->getCosts() as $id => $cost) {
                if (!$storage->offsetExists($cost->getGoodId())) {
                    $count = 0;
                    break;
                }
                if ($count * $cost->getCount() > $storage->offsetGet($cost->getGoodId())->getCount()) {
                    $count = floor($storage->offsetGet($cost->getGoodId())->getCount() / $cost->getCount());
                }
            }
            if ($count == 0) {
                continue;
            }
            foreach ($torp->getCosts() as $id => $cost) {
                $this->getColony()->lowerStorage($cost->getGoodId(), $cost->getCount() * $count);
            }
            $this->getColony()->upperStorage($torp->getGoodId(), $count * $torp->getAmount());
            $msg[] = sprintf(_('Es wurden %d Torpedos des Typs %s hergestellt'), $count * $torp->getAmount(),
                $torp->getName());
            $this->getColony()->lowerEps($count * $torp->getECost());
        }
        $this->getColony()->save();
        if (count($msg) > 0) {
            $this->addInformationMerge($msg);
        } else {
            $this->addInformation(_('Es wurden keine Torpedos hergestellt'));
        }
        DB()->commitTransaction();
    }

    /**
     */
    public function getTrainableCrewCountPerTick()
    {
        $count = currentUser()->getTrainableCrewCountMax() - currentUser()->getInTrainingCrewCount();
        if ($count > currentUser()->getCrewLeftCount()) {
            $count = currentUser()->getCrewLeftCount();
        }
        if ($count < 0) {
            $count = 0;
        }
        if ($count > $this->getColony()->getWorkless()) {
            return $this->getColony()->getWorkless();
        }
        return $count;
    }

    /**
     */
    protected function trainCrew()
    {
        $count = request::postStringFatal('crewcount');
        if ($count == INDICATOR_MAX) {
            $count = $this->getTrainableCrewCountPerTick();
        } else {
            if ($count > $this->getTrainableCrewCountPerTick()) {
                $count = $this->getTrainableCrewCountPerTick();
            } else {
                $count = intval($count);
            }
        }
        if ($count <= 0) {
            return;
        }
        if (!$this->getColony()->hasActiveBuildingWithFunction(BUILDING_FUNCTION_ACADEMY)) {
            $this->addInformation(_('Es befindet sich keine aktivierte Akademie auf diesen Planeten'));
            return;
        }
        if ($this->getTrainableCrewCountPerTick() <= 0) {
            $this->addInformation(_('Derzeit kann keine weitere Crew ausgebildet werden'));
            return;
        }
        DB()->beginTransaction();
        $i = 0;
        while ($i < $count) {
            $i++;
            $crew = new CrewTrainingData;
            $crew->setUserId(currentUser()->getId());
            $crew->setColonyId($this->getColony()->getId());
            $crew->save();
        }
        $this->getColony()->lowerWorkless($count);
        $this->getColony()->save();
        DB()->commitTransaction();
        $this->addInformation(sprintf(_('Es werden %d Crew ausgebildet'), $count));
    }

    /**
     */
    protected function repairShip()
    {
        $this->setFieldByRequest();
        $this->setView("SHOW_SHIP_REPAIR");
        // XXX TBD

        DB()->beginTransaction();
        $ship_id = request::getIntFatal('ship_id');
        $ship = ResourceCache()->getObject(CACHE_SHIP, $ship_id);
        if (!array_key_exists($ship->getId(), $this->getShiplistRepairable())) {
            return false;
        }
        if (!$ship->canBeRepaired()) {
            $this->addInformation(_('Das Schiff kann nicht repariert werden'));
            return;
        }
        if ($ship->getState() == SHIP_STATE_REPAIR) {
            $this->addInformation(_('Das Schiff wird bereits repariert'));
            return;
        }

        $obj = new ColonyShipRepair;
        $obj->setColonyId($this->getColony()->getId());
        $obj->setShipId($ship_id);
        $obj->setFieldId($this->getField()->getFieldId());
        $obj->save();

        $ship->setState(SHIP_STATE_REPAIR);
        $ship->save();

        DB()->commitTransaction();
        if (ColonyShipRepair::countInstances('colony_id=' . $this->getColony()->getId() . ' AND field_id=' . $this->getField()->getId()) > 1) {
            $this->addInformation(_('Das Schiff wurde zur Reparaturwarteschlange hinzugefügt'));
            return;
        }
        $ticks = ceil(($ship->getMaxHuell() - $ship->getHuell()) / $ship->getRepairRate());
        $this->addInformation(sprintf(_('Das Schiff wird repariert. Fertigstellung in %d Runden'), $ticks));
    }

}
