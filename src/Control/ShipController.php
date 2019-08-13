<?php

namespace Stu\Control;

use AccessViolation;
use Alliance;
use Building;
use ColFields;
use Colony;
use DatabaseEntry;
use DatabaseUser;
use DockingRights;
use DockingRightsData;
use Faction;
use Fleet;
use NavPanel;
use ObjectNotFoundException;
use PM;
use request;
use RumpColonizeBuilding;
use Ship;
use ShipAttackCycle;
use ShipCrew;
use ShipMover;
use ShipSingleAttackCycle;
use Stu\Lib\SessionInterface;
use SystemActivationWrapper;
use TradeLicencesData;
use TradeStorage;
use Tuple;
use User;
use VisualNavPanel;

final class ShipController extends GameController
{

    private $default_tpl = "html/ship.xhtml";
    public $destroyed_ships = array();

    private $session;

    public function __construct(
        SessionInterface $session
    )
    {
        $this->session = $session;
        parent::__construct($session, $this->default_tpl, "/ Schiffe");
        $this->addNavigationPart(new Tuple("shiplist.php", "Schiffe"));
        $this->addCallBack("B_ACTIVATE_CLOAK", "activateCloak", true);
        $this->addCallBack("B_DEACTIVATE_CLOAK", "deActivateCloak", true);
        $this->addCallBack("B_ACTIVATE_LSS", "activateLss");
        $this->addCallBack("B_DEACTIVATE_LSS", "deActivateLss");
        $this->addCallBack("B_ACTIVATE_NBS", "activateNbs");
        $this->addCallBack("B_DEACTIVATE_NBS", "deActivateNbs", true);
        $this->addCallBack("B_ACTIVATE_SHIELDS", "activateShield", true);
        $this->addCallBack("B_DEACTIVATE_SHIELDS", "deActivateShield", true);
        $this->addCallBack("B_ACTIVATE_PHASER", "activatePhaser", true);
        $this->addCallBack("B_DEACTIVATE_PHASER", "deActivatePhaser", true);
        $this->addCallBack("B_ACTIVATE_TORPEDO", "activateTorpedos", true);
        $this->addCallBack("B_DEACTIVATE_TORPEDO", "deActivateTorpedos", true);
        $this->addCallBack("B_CHANGE_NAME", "changeName", true);
        $this->addCallBack("B_LEAVE_STARSYSTEM", "leaveStarSystem", true);
        $this->addCallBack("B_ENTER_STARSYSTEM", "enterStarSystem", true);
        $this->addCallBack("B_MOVE", "moveShips", true);
        $this->addCallBack("B_MOVE_UP", "moveShipsUp", true);
        $this->addCallBack("B_MOVE_DOWN", "moveShipsDown", true);
        $this->addCallBack("B_MOVE_RIGHT", "moveShipsRight", true);
        $this->addCallBack("B_MOVE_LEFT", "moveShipsLeft", true);
        $this->addCallBack("B_JOIN_FLEET", "joinFleet");
        $this->addCallBack("B_ACTIVATE_WARP", "activateWarp", true);
        $this->addCallBack("B_DEACTIVATE_WARP", "deactivateWarp", true);
        $this->addCallBack("B_USE_BATTERY", "unloadBattery", true);
        $this->addCallBack("B_USE_BATTERY_MAX", "maxUnloadBattery", true);
        $this->addCallBack("B_ACTIVATE_TRAKTOR", "activateTraktorBeam", true);
        $this->addCallBack("B_DEACTIVATE_TRAKTOR", "deActivateTraktorBeam", true);
        $this->addCallBack("B_SET_GREEN_ALERT", "setGreenAlert", true);
        $this->addCallBack("B_SET_YELLOW_ALERT", "setYellowAlert", true);
        $this->addCallBack("B_SET_RED_ALERT", "setRedAlert", true);
        $this->addCallBack("B_ETRANSFER", "epsTransfer", true);
        $this->addCallBack("B_MAX_ETRANSFER", "maxEpsTransfer", true);
        $this->addCallBack("B_BEAMTO", "beamTo", true);
        $this->addCallBack("B_BEAMFROM", "beamFrom", true);
        $this->addCallBack("B_BEAMTO_COLONY", "beamToColony", true);
        $this->addCallBack("B_BEAMFROM_COLONY", "beamFromColony", true);
        $this->addCallBack("B_SELFDESTRUCT", "selfDestruct", true);
        $this->addCallBack("B_ATTACK_SHIP", 'attackShip', true);
        $this->addCallBack("B_INTERCEPT", "interceptShip", true);
        $this->addCallBack('B_ADD_DOCKPRIVILEGE', 'addDockPrivilege', true);
        $this->addCallBack('B_DELETE_DOCKPRIVILEGE', 'deleteDockPrivilege', true);
        $this->addCallBack('B_DOCK', 'dockShip', true);
        $this->addCallBack('B_UNDOCK', 'unDockShip', true);
        $this->addCallBack('B_PAY_TRADELICENCE', 'payTradeLicence', true);
        $this->addCallBack('B_TRANSFER_TO_ACCOUNT', 'transferToAccount', true);
        $this->addCallBack('B_TRANSFER_FROM_ACCOUNT', 'transferFromAccount', true);
        $this->addCallBack('B_HIDE_FLEET', 'hideFleet');
        $this->addCallBack('B_SHOW_FLEET', 'showFleet');
        $this->addCallBack('B_FLEET_ACTIVATE_NBS', 'fleetActivateNbs', true);
        $this->addCallBack('B_FLEET_DEACTIVATE_NBS', 'fleetDeactivateNbs', true);
        $this->addCallBack('B_FLEET_ACTIVATE_SHIELDS', 'fleetActivateShields', true);
        $this->addCallBack('B_FLEET_DEACTIVATE_SHIELDS', 'fleetDeactivateShields', true);
        $this->addCallBack('B_FLEET_ACTIVATE_PHASER', 'fleetActivatePhaser', true);
        $this->addCallBack('B_FLEET_DEACTIVATE_PHASER', 'fleetDeactivatePhaser', true);
        $this->addCallBack('B_FLEET_ACTIVATE_TORPEDO', 'fleetActivateTorpedos', true);
        $this->addCallBack('B_FLEET_DEACTIVATE_TORPEDO', 'fleetDeactivateTorpedos', true);
        $this->addCallBack('B_FLEET_ACTIVATE_CLOAK', 'fleetActivateCloak', true);
        $this->addCallBack('B_FLEET_DEACTIVATE_CLOAK', 'fleetDeactivateCloak', true);
        $this->addCallBack('B_FLEET_ACTIVATE_WARP', 'fleetActivateWarp', true);
        $this->addCallBack('B_FLEET_DEACTIVATE_WARP', 'fleetDeactivateWarp', true);
        $this->addCallBack('B_FLEET_ALERT_GREEN', 'fleetAlertGreen', true);
        $this->addCallBack('B_FLEET_ALERT_YELLOW', 'fleetAlertYellow', true);
        $this->addCallBack('B_FLEET_ALERT_RED', 'fleetAlertRed', true);
        $this->addCallBack('B_LOAD_WARPCORE', 'loadWarpcore');
        $this->addCallBack('B_LOAD_WARPCORE_MAX', 'loadWarpcoreMax');
        $this->addCallBack('B_ESCAPE_TRAKTOR', 'escapeTraktorBeam', true);
        $this->addCallBack('B_COLONIZE', 'colonize', true);
        $this->addCallBack('B_RENAME_CREW', 'renameCrew');
        $this->addCallBack('B_SALVAGE_EPODS', 'salvageEpods');

        $this->getTemplate()->setVar('currentShip', $this->getShip());

        $this->addView("SHOW_SHIP", "showShip");
        $this->addView("SHOW_ALVL", "showShipAlvl");
        $this->addView("SHOW_ETRANSFER", "showEpsTransfer");
        $this->addView("SHOW_BEAMTO", "showBeamTo");
        $this->addView("SHOW_BEAMFROM", "showBeamFrom");
        $this->addView("SHOW_COLONY_BEAMTO", "showBeamToColony");
        $this->addView("SHOW_COLONY_BEAMFROM", "showBeamFromColony");
        $this->addView("SHOW_SELFDESTRUCT_AJAX", "showSelfdestruct");
        $this->addView("SHOW_SCAN", "showScan");
        $this->addView("SHOW_SECTOR_SCAN", "showSectorScan");
        $this->addView("SHOW_SHIPDETAILS", "showShipDetails");
        $this->addView("SHOW_DOCKPRIVILEGE_CONFIG", 'showDockPrivilegeConfig');
        $this->addView('SHOW_DOCKPRIVILEGE_LIST', 'showDockPrivilegeList');
        $this->addView('SHOW_TRADEMENU', 'showTradeMenu');
        $this->addView('SHOW_TRADEMENU_CHOOSE_PAYMENT', 'showTradeMenuChoosePayment');
        $this->addView('SHOW_TRADEMENU_TRANSFER', 'showTradeMenuTransfer');
        $this->addView('SHOW_REGION_INFO', 'showRegionInfo');
        $this->addView('SHOW_COLONIZATION', 'showColonization');
        $this->addView('SHOW_RENAME_CREW', 'showRenameCrew');

        if (!$this->getView()) {
            $this->showShip();
        }
    }

    private $ship = null;

    protected function showShip()
    {
        $this->addNavigationPart(new Tuple("?id=" . $this->getShip()->getId(),
            $this->getShip()->getNameWithoutMarkup()));
        $this->setPagetitle("Schiff: " . $this->getShip()->getNameWithoutMarkup());
    }

    protected function fleetActivateNbs()
    {
        $msg = array();
        $msg[] = "Flottenbefehl ausgeführt: Aktivierung der Nahbereichssensoren";
        foreach ($this->getShip()->getFleet()->getShips() as $key => $ship) {
            if ($ship->getNbs()) {
                continue;
            }
            if ($ship->getEps() < SYSTEM_ECOST_NBS) {
                $msg[] = $ship->getName() . ": Nicht genügend Energie vorhanden";
                continue;
            }
            $ship->setNbs(1);
            $ship->lowerEps(SYSTEM_ECOST_NBS);
            $ship->save();
        }
        $this->addInformationMerge($msg);
    }

    protected function fleetDeactivateNbs()
    {
        foreach ($this->getShip()->getFleet()->getShips() as $key => $ship) {
            $ship->setNbs(0);
            $ship->save();
        }
        $this->addInformation("Flottenbefehl ausgeführt: Deaktivierung der Nahbereichssensoren");
    }

    protected function fleetActivateShields()
    {
        $msg = array();
        $msg[] = "Flottenbefehl ausgeführt: Aktivierung der Schilde";
        foreach ($this->getShip()->getFleet()->getShips() as $key => $ship) {
            if ($ship->shieldIsActive()) {
                continue;
            }
            if ($ship->getShield() < 1) {
                $msg[] = $ship->getName() . _(": Die Schilde sind nicht aufgeladen");
                continue;
            }
            if ($ship->cloakIsActive()) {
                $msg[] = $ship->getName() . ": Die Tarnung ist aktiviert";
                continue;
            }
            if ($ship->getEps() < SYSTEM_ECOST_SHIELDS) {
                $msg[] = $ship->getName() . ": Nicht genügend Energie vorhanden";
                continue;
            }
            if ($ship->isDocked()) {
                $msg[] = $ship->getName() . _(": Abgedockt");
                $ship->setDock(0);
            }
            $ship->cancelRepair();
            $ship->lowerEps(SYSTEM_ECOST_SHIELDS);
            $ship->setShieldState(1);
            $ship->save();
        }
        $this->addInformationMerge($msg);
    }

    protected function fleetDeactivateShields()
    {
        foreach ($this->getShip()->getFleet()->getShips() as $key => $ship) {
            $ship->setShieldState(0);
            $ship->save();
        }
        $this->addInformation("Flottenbefehl ausgeführt: Deaktivierung der Schilde");
    }

    protected function fleetActivatePhaser()
    {
        $msg = array();
        $msg[] = "Flottenbefehl ausgeführt: Aktivierung der Strahlenwaffen";
        foreach ($this->getShip()->getFleet()->getShips() as $key => $ship) {
            if (!$ship->hasPhaser() || $ship->phaserIsActive()) {
                continue;
            }
            if ($ship->getEps() < SYSTEM_ECOST_PHASER) {
                $msg[] = $ship->getName() . ": Nicht genügend Energie vorhanden";
                continue;
            }
            $ship->lowerEps(SYSTEM_ECOST_PHASER);
            $ship->setPhaser(1);
            $ship->save();
        }
        $this->addInformationMerge($msg);
    }

    protected function fleetDeactivatePhaser()
    {
        foreach ($this->getShip()->getFleet()->getShips() as $key => $ship) {
            $ship->setPhaser(0);
            $ship->save();
        }
        $this->addInformation("Flottenbefehl ausgeführt: Deaktivierung der Strahlenwaffen");
    }

    protected function fleetActivateTorpedos()
    {
        $msg = array();
        $msg[] = "Flottenbefehl ausgeführt: Aktivierung der Torpedobänke";
        foreach ($this->getShip()->getFleet()->getShips() as $key => $ship) {
            if (!$ship->hasTorpedo() || $ship->torpedoIsActive()) {
                continue;
            }
            if (!$ship->getTorpedoCount()) {
                $msg[] = $ship->getName() . _(": Keine Torpedos geladen");
                continue;
            }
            if ($ship->getEps() < SYSTEM_ECOST_TORPEDO) {
                $msg[] = $ship->getName() . _(": Nicht genügend Energie vorhanden");
                continue;
            }
            $ship->lowerEps(SYSTEM_ECOST_TORPEDO);
            $ship->setTorpedos(1);
            $ship->save();
        }
        $this->addInformationMerge($msg);
    }

    protected function fleetDeactivateTorpedos()
    {
        foreach ($this->getShip()->getFleet()->getShips() as $key => $ship) {
            $ship->setTorpedos(0);
            $ship->save();
        }
        $this->addInformation(_("Flottenbefehl ausgeführt: Deaktivierung der Torpedobänke"));
    }

    protected function fleetActivateCloak()
    {
        $msg = array();
        $msg[] = "Flottenbefehl ausgeführt: Aktivierung der Tarnung";
        foreach ($this->getShip()->getFleet()->getShips() as $key => $ship) {
            if (!$ship->isCloakable()) {
                continue;
            }
            if ($ship->getEps() < SYSTEM_ECOST_CLOAK) {
                $msg[] = $ship->getName() . _(": Nicht genügend Energie vorhanden");
                continue;
            }
            if ($ship->shieldIsActive()) {
                $ship->setShieldState(0);
                $msg[] = $ship->getName() . _(": Schilde deaktiviert");
            }
            if ($ship->isDocked()) {
                $ship->setDock(0);
                $msg[] = $ship->getName() . _(": Abgedockt");
            }
            $ship->lowerEps(SYSTEM_ECOST_CLOAK);
            $ship->setCloak(1);
            $ship->save();
        }
        $this->addInformationMerge($msg);
    }

    protected function fleetDeactivateCloak()
    {
        foreach ($this->getShip()->getFleet()->getShips() as $key => $ship) {
            $ship->setCloak(0);
            $ship->save();
        }
        $this->addInformation(_("Flottenbefehl ausgeführt: Deaktivierung der Tarnung"));
    }

    protected function fleetActivateWarp()
    {
        $msg = array();
        $msg[] = "Flottenbefehl ausgeführt: Aktivierung des Warpantriebs";
        foreach ($this->getShip()->getFleet()->getShips() as $key => $ship) {
            if (!$ship->isWarpable()) {
                continue;
            }
            // TBD Warpantrieb beschädigt
            if ($ship->isDocked()) {
                if ($ship->getEps() < SYSTEM_ECOST_DOCK) {
                    $msg[] = $ship->getName() . _(': Nicht genügend Energie zum Abdocken vorhanden');
                    continue;
                }
                $ship->setDock(0);
                $ship->lowerEps(SYSTEM_ECOST_DOCK);
                $ship->save();
            }
            if ($ship->getEps() < SYSTEM_ECOST_WARP) {
                $msg[] = $ship->getName() . _(": Nicht genügend Energie vorhanden");
                continue;
            }
            $ship->lowerEps(SYSTEM_ECOST_WARP);
            if ($ship->traktorBeamFromShip()) {
                if ($ship->getEps() < SYSTEM_ECOST_TRACTOR) {
                    $msg[] = $ship->getName() . _(": Traktorstrahl aufgrund von Energiemangel deaktiviert");
                    $ship->getTraktorShip()->unsetTraktor();
                    $ship->getTraktorShip()->save();
                    $ship->unsetTraktor();
                } else {
                    $ship->getTraktorShip()->setWarpState(1);
                    $ship->getTraktorShip()->save();
                    $ship->lowerEps(1);
                }
            }
            $ship->setWarpState(1);
            $ship->save();
        }
        $this->addInformationMerge($msg);
    }

    protected function fleetDeactivateWarp()
    {
        foreach ($this->getShip()->getFleet()->getShips() as $key => $ship) {
            if (!$ship->getWarpState()) {
                continue;
            }
            if ($ship->traktorBeamFromShip()) {
                $ship->getTraktorShip()->setWarpState(1);
                $ship->getTraktorShip()->save();
            }
            $ship->setWarpState(0);
            $ship->save();
        }
        $this->addInformation(_('Flottenbefehl ausgeführt: Deaktivierung des Warpantriebs'));
    }

    /**
     */
    protected function fleetAlertGreen()
    { #{{{
        $this->changeFleetAlertState(ALERT_GREEN);
        $this->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe Grün'));
    } # }}}

    /**
     */
    protected function fleetAlertYellow()
    { #{{{
        $this->changeFleetAlertState(ALERT_YELLOW);
        $this->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe Gelb'));
    } # }}}

    /**
     */
    protected function fleetAlertRed()
    { #{{{
        $this->changeFleetAlertState(ALERT_RED);
        $this->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe Rot'));
    } # }}}

    /**
     */
    private function changeFleetAlertState($state)
    { #{{{
        foreach ($this->getShip()->getFleet()->getShips() as $key => $ship) {
            $ship->setAlertState($state);
            $ship->save();
        }
    } # }}}

    protected function showShipDetails()
    {
        $this->setPageTitle("Schiffsinformationen");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/shipmacros.xhtml/shipdetails');
    }

    function showSelfdestruct()
    {
        $this->setPageTitle("Selbstzerstörung");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/shipmacros.xhtml/selfdestruct');
    }

    function showShipAlvl()
    {
        $this->setPageTitle("Alarmstufe ändern");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/shipmacros.xhtml/show_ship_alvl');
    }

    function showEpsTransfer()
    {
        $target = new Ship(request::getIntFatal('target'));
        $this->preChecks($target);
        $this->setPageTitle("Energietransfer");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/shipmacros.xhtml/show_ship_etransfer');
        $this->getTemplate()->setVar('targetShip', $target);
    }

    function showBeamTo()
    {
        $target = new Ship(request::getIntFatal('target'));
        $this->preChecks($target);
        $this->setPageTitle("Runterbeamen");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/shipmacros.xhtml/show_ship_beamto');
        $this->getTemplate()->setVar('targetShip', $target);
    }

    function showBeamFrom()
    {
        $target = new Ship(request::getIntFatal('target'));
        $this->preChecks($target);
        $this->setPageTitle("Hochbeamen");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/shipmacros.xhtml/show_ship_beamfrom');
        $this->getTemplate()->setVar('targetShip', $target);
    }

    function showBeamToColony()
    {
        $target = new Colony(request::getIntFatal('target'));
        $this->preChecks($target, true);
        $this->setPageTitle("Runterbeamen");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/shipmacros.xhtml/show_ship_beamto_colony');
        $this->getTemplate()->setVar('targetShip', $target);
    }

    function showBeamFromColony()
    {
        $target = new Colony(request::getIntFatal('target'));
        $this->preChecks($target, true);
        $this->setPageTitle("Hochbeamen");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/shipmacros.xhtml/show_ship_beamfrom_colony');
        $this->getTemplate()->setVar('targetShip', $target);
    }

    protected function showSectorScan()
    {
        $this->setPageTitle("Sektor Scan");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/shipmacros.xhtml/sectorscan');
        if ($this->getShip()->getCurrentMapField()->getFieldType()->getColonyClass()) {
            $this->checkDatabaseItem($this->getShip()->getCurrentMapField()->getFieldType()->getColonyType()->getDatabaseId());
        }
        if ($this->getShip()->getCurrentMapField()->getFieldType()->getIsSystem()) {
            $this->checkDatabaseItem($this->getShip()->getCurrentMapField()->getSystem()->getSystemType()->getDatabaseId());
        }
        if ($this->getShip()->isInSystem()) {
            $this->checkDatabaseItem($this->getShip()->getSystem()->getDatabaseId());
        }
    }

    protected function showScan()
    {
        $target = new Ship(request::getIntFatal('target'));
        $this->setPageTitle("Scan");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/shipmacros.xhtml/show_ship_scan');
        if (!checkPosition($this->getShip(), $target)) {
            $this->addInformation("Das Schiff befindet sich nicht in diesem Sektor");
            return;
        }
        $this->getTemplate()->setVar('targetShip', $target);
        if ($target->getDatabaseId()) {
            $this->checkDatabaseItem($target->getDatabaseId());
        }
        if ($target->getRump()->getDatabaseId()) {
            $this->checkDatabaseItem($target->getRump()->getDatabaseId());
        }
    }

    protected function preChecks(&$target, $colony = false)
    {
        if (!checkPosition($this->getShip(),
                $target) || $this->getShip()->getCloakState() || ($colony && $target->getId() == $this->getShip()->getId())) {
            new ObjectNotFoundException($target->getId());
        }
        if ($colony) {
            return true;
        }
        if ($target->shieldIsActive() && $target->getUserId() != currentUser()->getId()) {
            $this->addInformation("Die " . $target->getName() . " hat die Schilde aktiviert");
            return false;
        }
        return true;
    }

    public function getShip()
    {
        if ($this->ship === null) {
            $this->ship = Ship::getById(request::indInt('id'));
            if (!$this->ship->ownedByCurrentUser()) {
                $this->redirectTo('shiplist.php?B_NOT_OWNER=1&sstr=' . $this->getSessionString());
            }
        }
        return $this->ship;
    }

    private $navpanel = null;

    function getNavPanel()
    {
        if ($this->navpanel === null) {
            $this->navpanel = new NavPanel($this->getShip());
        }
        return $this->navpanel;
    }

    function getVisualNavPanel()
    {
        if ($this->navpanel === null) {
            $this->navpanel = new VisualNavPanel($this->getShip());
        }
        return $this->navpanel;
    }

    function activateCloak()
    {
        $wrapper = new SystemActivationWrapper($this->getShip());
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $this->addInformation($wrapper->getError());
            return;
        }
        if ($this->getShip()->shieldIsActive()) {
            $this->getShip()->setShieldState(0);
            $this->addInformation("Schilde deaktiviert");
        }
        if ($this->getShip()->isDocked()) {
            $this->addInformation('Das Schiff hat abgedockt');
            $this->getShip()->setDock(0);
        }
        $this->getShip()->setCloak(1);
        $this->getShip()->save();
        $this->addInformation("Tarnung aktiviert");
    }

    function deactivateCloak()
    {
        $this->getShip()->setCloak(0);
        $this->getShip()->save();
        $this->addInformation("Tarnung deaktiviert");
        // $this->redalert();
    }

    function leaveStarSystem()
    {
        if (!$this->getShip()->isInSystem()) {
            return;
        }
        $wrapper = new SystemActivationWrapper($this->getShip());
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $this->addInformation($wrapper->getError());
            return;
        }
        $this->getShip()->cancelRepair();
        $this->getShip()->leaveStarSystem();
        if ($this->getShip()->isTraktorbeamActive()) {
            $this->leaveStarSystemTraktor($this->getShip());
        }
        if ($this->getShip()->isFleetLeader()) {
            $msg = array();
            $result = Fleet::getShipsBy($this->getShip()->getFleetId(), array($this->getShip()->getId()));
            foreach ($result as $key => $ship) {
                $wrapper = new SystemActivationWrapper($ship);
                $wrapper->setVar('eps', 1);
                if ($wrapper->getError()) {
                    $msg[] = "Die " . $ship->getName() . " hat die Flotte verlassen. Grund: " . $wrapper->getError();
                    $ship->leaveFleet();
                } else {
                    $ship->leaveStarSystem();
                    if ($ship->isTraktorbeamActive()) {
                        $this->leaveStarSystemTraktor($ship);
                    }
                }
                $ship->save();
            }
            $this->addInformation("Die Flotte hat das Sternensystem verlassen");
            $this->addInformationMerge($msg);
        } else {
            if ($this->getShip()->isInFleet()) {
                $this->getShip()->leaveFleet();
                $this->addInformation("Das Schiff hat die Flotte verlassen");
            }
            $this->addInformation("Das Sternensystem wurde verlassen");
        }
        $this->getShip()->save();
    }

    function enterStarSystem()
    {
        if (!$this->getShip()->isOverSystem()) {
            return;
        }
        $wrapper = new SystemActivationWrapper($this->getShip());
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $this->addInformation($wrapper->getError());
            return;
        }
        switch ($this->getShip()->getFlightDirection()) {
            case 1:
                $posx = rand(1, $this->getShip()->getSystem()->getMaxX());
                $posy = 1;
                break;
            case 2:
                $posx = rand(1, $this->getShip()->getSystem()->getMaxX());
                $posy = $this->getShip()->getSystem()->getMaxY();
                break;
            case 3:
                $posx = 1;
                $posy = rand(1, $this->getShip()->getSystem()->getMaxY());
                break;
            case 4:
                $posx = $this->getShip()->getSystem()->getMaxX();
                $posy = rand(1, $this->getShip()->getSystem()->getMaxY());
                break;
        }
        // XXX TBD Beschädigung bei Systemeinflug
        $this->getShip()->enterStarSystem($this->getShip()->getSystem()->getId(), $posx, $posy);
        if ($this->getShip()->isTraktorbeamActive()) {
            $this->enterStarSystemTraktor($this->getShip());
        }
        if ($this->getShip()->isFleetLeader()) {
            $msg = array();
            $result = Fleet::getShipsBy($this->getShip()->getFleetId(), array($this->getShip()->getId()));
            foreach ($result as $key => $ship) {
                $wrapper = new SystemActivationWrapper($ship);
                $wrapper->setVar('eps', 1);
                if ($wrapper->getError()) {
                    $msg[] = "Die " . $ship->getName() . " hat die Flotte verlassen. Grund: " . $wrapper->getError();
                    $ship->leaveFleet();
                } else {
                    $ship->enterStarSystem($this->getShip()->getSystem()->getId(), $posx, $posy);
                    if ($ship->isTraktorbeamActive()) {
                        $this->enterStarSystemTraktor($ship);
                    }
                }
                $ship->save();
            }
            $this->addInformation("Die Flotte fliegt in das " . $this->getShip()->getSystem()->getName() . "-System ein");
            $this->addInformationMerge($msg);
        } else {
            if ($this->getShip()->isInFleet()) {
                $this->getShip()->leaveFleet();
                $this->addInformation("Das Schiff hat die Flotte verlassen");
            }
            $this->addInformation("Das Schiff fliegt in das " . $this->getShip()->getSystem()->getName() . "-System ein");
        }
        $this->getShip()->save();
    }

    function enterStarSystemTraktor(&$ship)
    {
        if ($ship->getEps() < 1) {
            $ship->getTraktorShip()->unsetTraktor();
            $ship->getTraktorShip()->save();
            $ship->unsetTraktor();
            $this->addInformation("Der Traktorstrahl auf die " . $ship->getTraktorShip()->getName() . " wurde beim Systemeinflug aufgrund Energiemangels deaktiviert");
            return;
        }
        $ship->getTraktorShip()->enterStarSystem($ship->getSystem()->getId(), $ship->getPosX(), $ship->getPosY());
        // TBD Beschädigung bei Systemeinflug
        $ship->lowerEps(1);
        $ship->getTraktorShip()->save();
        $this->getShip()->save();
        $this->addInformation("Die " . $ship->getTraktorShip()->getName() . " wurde mit in das System gezogen");
    }

    function leaveStarSystemTraktor(&$ship)
    {
        if ($ship->getEps() < 1) {
            $ship->getTraktorShip()->unsetTraktor();
            $ship->getTraktorShip()->save();
            $ship->unsetTraktor();
            $this->addInformation("Der Traktorstrahl auf die " . $ship->getTraktorShip()->getName() . " wurde beim Verlassen des Systems aufgrund Energiemangels deaktiviert");
            return;
        }
        $ship->getTraktorShip()->leaveStarSystem();
        $ship->lowerEps(1);
        $ship->getTraktorShip()->save();
        $this->getShip()->save();
        $this->addInformation("Die " . $ship->getTraktorShip()->getName() . " wurde mit aus dem System gezogen");
    }

    function activateLss()
    {
        $wrapper = new SystemActivationWrapper($this->getShip());
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $this->addInformation($wrapper->getError());
            return;
        }
        $this->getShip()->setLss(1);
        $this->getShip()->save();
        $this->addInformation("Langstreckensensoren aktiviert");
    }

    function deactivateLss()
    {
        $this->getShip()->setLss(0);
        $this->getShip()->save();
        $this->addInformation("Langstreckensensoren deaktiviert");
    }

    function changeName()
    {
        $value = request::postString('shipname');
        $this->getShip()->setName(tidyString($value));
        $this->getShip()->save();
        $this->addInformation("Der Schiffname wurde geändert");
    }

    protected function activateShield()
    {
        if ($this->getShip()->cloakIsActive()) {
            $this->addInformation("Die Tarnung ist aktiviert");
            return;
        }
        if ($this->getShip()->isTraktorbeamActive()) {
            $this->addInformation(_("Der Traktorstrahl ist aktiviert"));
            return;
        }
        $wrapper = new SystemActivationWrapper($this->getShip());
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $this->addInformation($wrapper->getError());
            return;
        }
        if ($this->getShip()->getShield() <= 1) {
            $this->addInformation("Schilde sind nicht aufgeladen");
            return;
        }
        $this->getShip()->cancelRepair();
        if ($this->getShip()->isDocked()) {
            $this->addInformation('Das Schiff hat abgedockt');
            $this->getShip()->setDock(0);
        }
        $this->getShip()->setShieldState(1);
        $this->getShip()->save();
        $this->addInformation("Schilde aktiviert");
    }

    function deactivateShield()
    {
        $this->getShip()->setShieldState(0);
        $this->getShip()->save();
        $this->addInformation("Schilde deaktiviert");
    }

    protected function activatePhaser()
    {
        if (!$this->getShip()->hasPhaser() || $this->getShip()->phaserIsActive()) {
            return;
        }
        $wrapper = new SystemActivationWrapper($this->getShip());
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $this->addInformation($wrapper->getError());
            return;
        }
        $this->getShip()->setPhaser(1);
        $this->getShip()->save();
        $this->addInformation("Strahlenwaffe aktiviert");
    }

    function deactivatePhaser()
    {
        $this->getShip()->setPhaser(0);
        $this->getShip()->save();
        $this->addInformation("Strahlenwaffe deaktiviert");
    }

    protected function activateTorpedos()
    {
        if (!$this->getShip()->hasTorpedo() || $this->getShip()->torpedoIsActive()) {
            return;
        }
        if ($this->getShip()->getTorpedoCount() == 0) {
            $this->addInformation("Das Schiff hat keine Torpedos geladen");
            return;
        }
        $wrapper = new SystemActivationWrapper($this->getShip());
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $this->addInformation($wrapper->getError());
            return;
        }
        $this->getShip()->setTorpedos(1);
        $this->getShip()->save();
        $this->addInformation("Torpedobänke aktiviert");
    }

    function deactivateTorpedos()
    {
        $this->getShip()->setTorpedos(0);
        $this->getShip()->save();
        $this->addInformation("Torpedobänke deaktiviert");
    }

    function activateWeapons()
    {
        $this->activatePhaser();
        $this->activateTorpedos();
    }

    function activateNbs()
    {
        $wrapper = new SystemActivationWrapper($this->getShip());
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $this->addInformation($wrapper->getError());
            return;
        }
        $this->getShip()->setNbs(1);
        $this->getShip()->save();
        $this->addInformation("Nahbereichssensoren aktiviert");
    }

    function deactivateNbs()
    {
        $this->getShip()->setNbs(0);
        $this->getShip()->save();
        $this->addInformation("Nahbereichssensoren deaktiviert");
    }

    function getNbs($query)
    {
        $result = DB()->query($query);
        $ret = array();
        while ($data = mysqli_fetch_assoc($result)) {
            $ret[] = ResourceCache()->getObject('ship', $data['id']);
        }
        return $ret;
    }

    private function getFNbs($query)
    {
        $result = DB()->query($query);
        $ret = array();
        while ($data = mysqli_fetch_assoc($result)) {
            $obj = new Ship($data['id']);
            if (!array_key_exists($obj->getFleetId(), $ret)) {
                $ret[$obj->getFleetId()]['fleet'] = new Fleet($obj->getFleetId());
                if ($this->session->hasSessionValue('hiddenfleets', $obj->getFleetId())) {
                    $ret[$obj->getFleetId()]['fleethide'] = true;
                } else {
                    $ret[$obj->getFleetId()]['fleethide'] = false;
                }
            }
            $ret[$obj->getFleetId()]['ships'][] = $obj;
        }
        return $ret;
    }

    private $singleShipsNbs = null;
    private $fleetNbs = null;
    private $stationsNbs = null;

    function getFleetsNbs()
    {
        if ($this->fleetNbs === null) {
            $this->fleetNbs = $this->getFNbs("SELECT id FROM stu_ships WHERE id!=" . $this->getShip()->getId() . " AND is_base=0 AND fleets_id>0 AND cloak=0
				AND " . ($this->getShip()->isInSystem() ? "systems_id=" . $this->getShip()->getSystemsId() . " AND sx=" . $this->getShip()->getPosX() . " AND sy=
				" . $this->getShip()->getPosY() : "systems_id=0 AND cx=" . $this->getShip()->getPosX() . " AND cy=" . $this->getShip()->getPosY()));
        }
        return $this->fleetNbs;
    }

    function getStationsNbs()
    {
        if ($this->stationsNbs === null) {
            $this->stationsNbs = $this->getNbs("SELECT id FROM stu_ships WHERE id!=" . $this->getShip()->getId() . " AND is_base=1 AND cloak=0
				AND " . ($this->getShip()->isInSystem() ? "systems_id=" . $this->getShip()->getSystemsId() . " AND sx=" . $this->getShip()->getPosX() . " AND sy=
				" . $this->getShip()->getPosY() : "systems_id=0 AND cx=" . $this->getShip()->getPosX() . " AND cy=" . $this->getShip()->getPosY()));
        }
        return $this->stationsNbs;
    }

    function getSingleShipsNbs()
    {
        if ($this->singleShipsNbs === null) {
            $this->singleShipsNbs = $this->getNbs("SELECT id FROM stu_ships WHERE id!=" . $this->getShip()->getId() . " AND is_base=0 AND fleets_id=0 AND cloak=0
				AND " . ($this->getShip()->isInSystem() ? "systems_id=" . $this->getShip()->getSystemsId() . " AND sx=" . $this->getShip()->getPosX() . " AND sy=
				" . $this->getShip()->getPosY() : "systems_id=0 AND cx=" . $this->getShip()->getPosX() . " AND cy=" . $this->getShip()->getPosY()));
        }
        return $this->singleShipsNbs;
    }

    function hasSingleShipsNbs()
    {
        return count($this->getSingleShipsNbs()) > 0;
    }

    function hasStationsNbs()
    {
        return count($this->getStationsNbs()) > 0;
    }

    function hasFleetsNbs()
    {
        return count($this->getFleetsNbs()) > 0;
    }

    function hasNbs()
    {
        return $this->hasSingleShipsNbs() || $this->hasStationsNbs() || $this->hasFleetsNbs();
    }

    function moveShips()
    {
        $mover = new ShipMover($this->getShip());
        $this->addInformationMerge($mover->getInformations());
    }

    function moveShipsUp()
    {
        $fields = request::postString('navapp');
        if ($fields <= 0 || $fields > 9 || strlen($fields) > 1) {
            $fields = 1;
        }
        request::setVar('posy', $this->getShip()->getPosY() - $fields);
        request::setVar('posx', $this->getShip()->getPosX());
        $this->moveShips();
    }

    function moveShipsDown()
    {
        $fields = request::postString('navapp');
        if ($fields <= 0 || $fields > 9 || strlen($fields) > 1) {
            $fields = 1;
        }
        request::setVar('posy', $this->getShip()->getPosY() + $fields);
        request::setVar('posx', $this->getShip()->getPosX());
        $this->moveShips();
    }

    function moveShipsLeft()
    {
        $fields = request::postString('navapp');
        if ($fields <= 0 || $fields > 9 || strlen($fields) > 1) {
            $fields = 1;
        }
        request::setVar('posy', $this->getShip()->getPosY());
        request::setVar('posx', $this->getShip()->getPosX() - $fields);
        $this->moveShips();
    }

    function moveShipsRight()
    {
        $fields = request::postString('navapp');
        if ($fields <= 0 || $fields > 9 || strlen($fields) > 1) {
            $fields = 1;
        }
        request::setVar('posy', $this->getShip()->getPosY());
        request::setVar('posx', $this->getShip()->getPosX() + $fields);
        $this->moveShips();
    }

    function joinFleet()
    {
        if ($this->getShip()->isInFleet()) {
            return;
        }
        // TBD zusätzliche checks für flotten
        $fleet = Fleet::getUserFleetById(request::getIntFatal('fleetid'));
        if ($fleet->getFleetLeader() == $this->getShip()->getId()) {
            return;
        }
        if (!checkPosition($fleet->getLeadShip(), $this->getShip())) {
            return;
        }
        if ($fleet->getPointSum() + $this->getShip()->getRump()->getCategory()->getPoints() > POINTS_PER_FLEET) {
            $this->addInformation(sprintf(_('Es sind maximal %d Schiffspunkte pro Flotte möglich'), POINTS_PER_FLEET));
            return;
        }
        $this->getShip()->setFleetId($fleet->getId());
        $this->getShip()->save();
        $this->addInformation(sprintf(_("Die %s ist der Flotte %s beigetreten"), $this->getShip()->getName(),
            $fleet->getName()));
    }

    function activateWarp()
    {
        if ($this->getShip()->getWarpState()) {
            return;
        }
        if (!$this->getShip()->isWarpAble()) {
            return;
        }
        // TBD Warpantrieb beschädigt
        $wrapper = new SystemActivationWrapper($this->getShip());
        $wrapper->setVar('eps', 1);
        if ($wrapper->getError()) {
            $this->addInformation($wrapper->getError());
            return;
        }
        if ($this->getShip()->isDocked()) {
            $this->addInformation('Das Schiff hat abgedockt');
            $this->getShip()->setDock(0);
        }
        if ($this->getShip()->traktorBeamFromShip()) {
            if ($this->getShip()->getEps() == 0) {
                $this->addInformation("Der Traktorstrahl zur " . $this->getShip()->getTraktorShip()->getName() . " wurde aufgrund von Energiemangel deaktiviert");
                $this->getShip()->getTraktorShip()->unsetTraktor();
                $this->getShip()->getTraktorShip()->save();
                $this->getShip()->unsetTraktor();
            } else {
                $this->getShip()->getTraktorShip()->setWarpState(1);
                $this->getShip()->getTraktorShip()->save();
                $this->getShip()->lowerEps(1);
            }
        }
        $this->getShip()->setWarpState(1);
        $this->getShip()->save();
        $this->addInformation("Die " . $this->getShip()->getName() . " hat den Warpantrieb aktiviert");
    }

    function deactivateWarp()
    {
        if (!$this->getShip()->getWarpState()) {
            return;
        }
        if ($this->getShip()->traktorBeamFromShip()) {
            $this->getShip()->getTraktorShip()->setWarpState(0);
            $this->getShip()->getTraktorShip()->save();
        }
        // TBD Alarm Rot
        $this->getShip()->setWarpState(0);
        $this->getShip()->save();
        $this->addInformation("Die " . $this->getShip()->getName() . " hat den Warpantrieb deaktiviert");
    }

    protected function unloadBattery($load = 0)
    {
        $wrapper = new SystemActivationWrapper($this->getShip());
        if ($wrapper->getError()) {
            $this->addInformation($wrapper->getError());
            return;
        }
        if (!$this->getShip()->hasEmergencyBattery()) {
            return;
        }
        if (!$this->getShip()->getEBatt()) {
            $this->addInformation(_("Die Ersatzbatterie ist leer"));
            return;
        }
        if (!$this->getShip()->isEBattUseable()) {
            $this->addInformation("Die Batterie kann erst wieder am " . $this->getShip()->getEBattWaitingTime() . " genutzt werden");
            return;
        }
        if ($this->getShip()->getEps() >= $this->getShip()->getMaxEps()) {
            $this->addInformation("Der Energiespeicher ist voll");
            return;
        }
        if ($load == 0) {
            $load = request::postInt('ebattload');
        }
        // TBD load errechnen
        if ($load < 1) {
            return;
        }
        if ($load > $this->getShip()->getEBatt()) {
            $load = $this->getShip()->getEBatt();
        }
        if ($load + $this->getShip()->getEps() > $this->getShip()->getMaxEps()) {
            $load = $this->getShip()->getMaxEps() - $this->getShip()->getEps();
        }
        $this->getShip()->lowerEBatt($load);
        $this->getShip()->upperEps($load);
        $this->getShip()->setEBattWaitingTime($load * 60);
        $this->getShip()->save();
        $this->addInformation("Die Batterie wurde um " . $load . " Einheiten entladen");
    }

    protected function maxUnloadBattery()
    {
        $this->unloadBattery($this->getShip()->getEBatt());
    }

    protected function activateTraktorBeam()
    {
        if ($this->getShip()->isTraktorBeamActive()) {
            return;
        }
        if ($this->getShip()->shieldIsActive()) {
            $this->addInformation("Die Schilde sind aktiviert");
            return;
        }
        if ($this->getShip()->isDocked()) {
            $this->addInformation("Das Schiff ist angedockt");
            return;
        }
        $wrapper = new SystemActivationWrapper($this->getShip());
        $wrapper->setVar('eps', 2);
        if ($wrapper->getError()) {
            $this->addInformation($wrapper->getError());
            return;
        }
        $ship = Ship::getById(request::getIntFatal('target'));
        if ($ship->getRump()->isTrumfield()) {
            $this->addInformation("Das Trümmerfeld kann nicht erfasst werden");
            return;
        }
        if (!checkPosition($this->getShip(), $ship)) {
            return;
        }
        if ($ship->isBase()) {
            $this->addInformation("Die " . $ship->getName() . " kann nicht erfasst werden");
            return;
        }
        if ($ship->traktorbeamToShip()) {
            $this->addInformation("Das Schiff wird bereits vom Traktorstrahl der " . $ship->getTraktorShip()->getName() . " gehalten");
            return;
        }
        if ($ship->isInFleet() && $ship->getFleetId() == $this->getShip()->getFleetId()) {
            $this->addInformation("Die " . $ship->getName() . " befindet sich in der selben Flotte wie die " . $this->getShip()->getName());
            return;
        }
        if (($ship->getAlertState() == ALERT_YELLOW || $ship->getAlertState() == ALERT_RED) && !$ship->getUser()->isFriend(currentUser()->getId())) {
            if ($ship->isInFleet()) {
                $attacker = $ship->getFleet()->getShips();
            } else {
                $attacker = &$ship;
            }
            $obj = new ShipSingleAttackCycle($attacker, $this->getShip(), $ship->getFleetId(),
                $this->getShip()->getFleetId());
            $this->addInformationMergeDown($obj->getMessages());
            PM::sendPM(currentUser()->getId(), $ship->getUserId(),
                "Die " . $this->getShip()->getName() . " versucht die " . $ship->getName() . " in Sektor " . $this->getShip()->getSectorString() . " mit dem Traktorstrahl zu erfassen. Folgende Aktionen wurden ausgeführt:\n" . infoToString($obj->getMessages()),
                PM_SPECIAL_SHIP);
        }
        if ($ship->shieldIsActive()) {
            $this->addInformation("Die " . $ship->getName() . " kann aufgrund der aktiven Schilde nicht erfasst werden");
            return;
        }
        $ship->deactivateTraktorBeam();
        $this->getShip()->setTraktorMode(1);
        $this->getShip()->setTraktorShipId($ship->getId());
        $ship->setTraktorMode(2);
        $ship->setTraktorShipId($this->getShip()->getId());
        $this->getShip()->save();
        $ship->save();
        if (currentUser()->getId() != $ship->getUserId()) {
            PM::sendPM(currentUser()->getId(), $ship->getUserId(),
                "Die " . $ship->getName() . " wurde in SeKtor " . $this->getShip()->getSectorString() . " vom Traktorstrahl der " . $this->getShip()->getName() . " erfasst",
                PM_SPECIAL_SHIP);
        }
        $this->addInformation("Der Traktorstrahl wurde auf die " . $ship->getName() . " gerichtet");
    }

    protected function deactivateTraktorBeam()
    {
        if (!$this->getShip()->isTraktorBeamActive()) {
            return;
        }
        if ($this->getShip()->getTraktorMode() == 2) {
            return;
        }
        if (currentUser()->getId() != $this->getShip()->getTraktorShip()->getUserId()) {
            PM::sendPM(currentUser()->getId(), $this->getShip()->getTraktorShip()->getUserId(),
                "Der auf die " . $this->getShip()->getTraktorShip()->getName() . " gerichtete Traktorstrahl wurde in SeKtor " . $this->getShip()->getSectorString() . " deaktiviert",
                PM_SPECIAL_SHIP);
        }
        $this->getShip()->deactivateTraktorBeam();
        $this->addInformation("Der Traktorstrahl wurde deaktiviert");
    }

    protected function setGreenAlert()
    {
        $this->getShip()->setAlertState(1);
        $this->getShip()->save();
        $this->addInformation("Die Alarmstufe wurde auf Grün geändert");
    }

    protected function setYellowAlert()
    {
        $this->getShip()->setAlertState(2);
        $this->getShip()->save();
        $this->addInformation("Die Alarmstufe wurde auf Gelb geändert");
    }

    protected function setRedAlert()
    {
        $this->getShip()->setAlertState(3);
        $this->addInformation("Die Alarmstufe wurde auf Rot geändert");
        if (!$this->getShip()->shieldIsActive()) {
            $this->activateShield();
        }
        $this->activateWeapons();
        $this->getShip()->save();
    }

    /**
     */
    private function prepareForAction()
    { #{{{
        $wrapper = new SystemActivationWrapper($this->getShip());
        if ($wrapper->getError()) {
            $this->addInformation($wrapper->getError());
            return false;
        }
        return true;
    } # }}}

    protected function epsTransfer($load = 0)
    {
        if (!$this->prepareForAction()) {
            return;
        }
        if ($this->getShip()->getEps() == 0) {
            $this->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($this->getShip()->getCloakState()) {
            $this->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($this->getShip()->getWarpState()) {
            $this->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        $target = new Ship(request::postIntFatal('target'));
        if (!$this->preChecks($target)) {
            return;
        }
        if ($target->getWarpState()) {
            $this->addInformation(sprintf(_('Die %s befindet sich im Warp'), $target->getName()));
            return;
        }
        if ($load == 0) {
            $load = request::postInt('ecount');
        }
        if ($load < 1) {
            $this->addInformation(_("Es wurde keine Energiemenge angegeben"));
            return;
        }
        if ($target->getEBatt() >= $target->getMaxEBatt()) {
            $this->addInformation(sprintf(_('Der Energiespeicher der %s ist voll'), $target->getName()));
            return;
        }
        if ($load * 3 > $this->getShip()->getEps()) {
            $load = floor($this->getShip()->getEps() / 3);
        }
        if ($load + $target->getEbatt() > $target->getMaxEbatt()) {
            $load = $target->getMaxEbatt() - $target->getEbatt();
        }
        $this->getShip()->lowerEps($load * 3);
        $target->upperEbatt($load);
        $target->save();
        $this->getShip()->save();
        PM::sendPM(currentUser()->getId(), $target->getUserId(),
            "Die " . $this->getShip()->getName() . " transferiert in SeKtor " . $this->getShip()->getSectorString() . " " . $load . " Energie in die Batterie der " . $target->getName(),
            PM_SPECIAL_TRADE);
        $this->addInformation(sprintf(_('Es wurde %d Energie zur %s transferiert'), $load, $target->getName()));
    }

    function maxEpsTransfer()
    {
        $this->epsTransfer(floor($this->getShip()->getEps() / 3));
    }

    protected function beamTo()
    {
        if (!$this->prepareForAction()) {
            return;
        }
        if ($this->getShip()->getEps() == 0) {
            $this->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($this->getShip()->getCloakState()) {
            $this->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($this->getShip()->getWarpState()) {
            $this->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        $target = new Ship(request::postIntFatal('target'));
        if (!$this->preChecks($target)) {
            return;
        }
        if ($target->getWarpState()) {
            $this->addInformation(sprintf(_('Die %s befindet sich im Warp'), $target->getName()));
            return;
        }
        if (!$target->storagePlaceLeft()) {
            $this->addInformation(sprintf(_('Der Lagerraum der %s ist voll'), $target->getName()));
            return;
        }
        $goods = request::postArray('goods');
        $gcount = request::postArray('count');
        if ($this->getShip()->getStorage()->count() == 0) {
            $this->addInformation(_("Keine Waren zum Beamen vorhanden"));
            return;
        }
        if (count($goods) == 0 || count($gcount) == 0) {
            $this->addInformation(_("Es wurde keine Waren zum Beamen ausgewählt"));
            return;
        }
        $this->addInformation(sprintf(_('Die %s hat folgende Waren zur %s transferiert'), $this->getShip()->getName(),
            $target->getName()));
        foreach ($goods as $key => $value) {
            if ($this->getShip()->getEps() < 1) {
                break;
            }
            if (!array_key_exists($key, $gcount) || $gcount[$key] < 1) {
                continue;
            }
            if (!$this->getShip()->getStorage()->offsetExists($value)) {
                continue;
            }
            $count = $gcount[$key];
            $good = $this->getShip()->getStorage()->offsetGet($value);
            if (!$good->getGood()->isBeamable()) {
                $this->addInformation(sprintf(_('%s ist nicht beambar', $good->getGood()->getName())));
                continue;
            }
            if ($count == "m") {
                $count = $good->getAmount();
            } else {
                $count = intval($count);
            }
            if ($count < 1) {
                continue;
            }
            if ($target->getStorageSum() >= $target->getMaxStorage()) {
                break;
            }
            if ($count > $good->getAmount()) {
                $count = $good->getAmount();
            }
            if (ceil($count / $good->getGood()->getTransferCount()) > $this->getShip()->getEps()) {
                $count = $this->getShip()->getEps() * $good->getGood()->getTransferCount();
            }
            if ($target->getStorageSum() + $count > $target->getMaxStorage()) {
                $count = $target->getMaxStorage() - $target->getStorageSum();
            }
            $this->addInformation(sprintf(_('%d %s (Energieverbrauch: %d)'), $count, $good->getGood()->getName(),
                ceil($count / $good->getGood()->getTransferCount())));

            $this->getShip()->lowerStorage($value, $count);
            $target->upperStorage($value, $count);
            $this->getShip()->lowerEps(ceil($count / $good->getGood()->getTransferCount()));
            $target->setStorageSum($target->getStorageSum() + $count);
        }
        if ($target->getUserId() != $this->getShip()->getUserId()) {
            $this->sendInformation($target->getUserId(), $this->getShip()->getUserId(), PM_SPECIAL_TRADE);
        }
        $this->getShip()->save();
    }

    protected function beamFrom()
    {
        if (!$this->prepareForAction()) {
            return;
        }
        if ($this->getShip()->getEps() == 0) {
            $this->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($this->getShip()->getCloakState()) {
            $this->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($this->getShip()->getWarpState()) {
            $this->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        if ($this->getShip()->getShieldState()) {
            $this->addInformation(_("Die Schilde sind aktiviert"));
            return;
        }
        $target = new Ship(request::postIntFatal('target'));
        if (!$this->preChecks($target)) {
            return;
        }
        if ($target->getWarpState()) {
            $this->addInformation(sprintf(_('Die %s befindet sich im Warp'), $target->getName()));
            return;
        }
        if (!$this->getShip()->storagePlaceLeft()) {
            $this->addInformation(sprintf(_('Der Lagerraum der %s ist voll'), $this->getShip()->getName()));
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
        $this->addInformation(sprintf(_('Die %s hat folgende Waren von der %s transferiert'),
            $this->getShip()->getName(), $target->getName()));
        foreach ($goods as $key => $value) {
            if ($this->getShip()->getEps() < 1) {
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
                $this->addInformation(sprintf(_('%s ist nicht beambar', $good->getGood()->getName())));
                continue;
            }
            if ($count == "m") {
                $count = $good->getAmount();
            } else {
                $count = intval($count);
            }
            if ($count < 1) {
                continue;
            }
            if ($this->getShip()->getStorageSum() >= $this->getShip()->getMaxStorage()) {
                break;
            }
            if ($count > $good->getAmount()) {
                $count = $good->getAmount();
            }
            if (ceil($count / $good->getGood()->getTransferCount()) > $this->getShip()->getEps()) {
                $count = $this->getShip()->getEps() * $good->getGood()->getTransferCount();
            }
            if ($this->getShip()->getStorageSum() + $count > $this->getShip()->getMaxStorage()) {
                $count = $this->getShip()->getMaxStorage() - $this->getShip()->getStorageSum();
            }
            $this->addInformation(sprintf(_('%d %s (Energieverbrauch: %d)'), $count, $good->getGood()->getName(),
                ceil($count / $good->getGood()->getTransferCount())));

            $target->lowerStorage($value, $count);
            $this->getShip()->upperStorage($value, $count);
            $this->getShip()->lowerEps(ceil($count / $good->getGood()->getTransferCount()));
            $this->getShip()->setStorageSum($this->getShip()->getStorageSum() + $count);
        }
        if ($target->getUserId() != $this->getShip()->getUserId()) {
            $this->sendInformation($target->getUserId(), $this->getShip()->getUserId(), PM_SPECIAL_TRADE);
        }
        $this->getShip()->save();
    }

    function beamToColony()
    {
        $wrapper = new SystemActivationWrapper($this->getShip());
        if ($wrapper->getError()) {
            $this->addInformation($wrapper->getError());
            return;
        }
        if ($this->getShip()->getEps() == 0) {
            $this->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($this->getShip()->getCloakState()) {
            $this->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($this->getShip()->getWarpState()) {
            $this->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        $target = new Colony(request::postIntFatal('target'));
        if (!$this->preChecks($target, true)) {
            return;
        }
        if (!$target->storagePlaceLeft()) {
            $this->addInformation(sprintf(_('Der Lagerraum der Kolonie %s ist voll'), $target->getName()));
            return;
        }
        $goods = request::postArray('goods');
        $gcount = request::postArray('count');
        if ($this->getShip()->getStorage()->count() == 0) {
            $this->addInformation(_("Keine Waren zum Beamen vorhanden"));
            return;
        }
        if (count($goods) == 0 || count($gcount) == 0) {
            $this->addInformation(_("Es wurde keine Waren zum Beamen ausgewählt"));
            return;
        }
        $this->addInformation(sprintf(_('Die %s hat folgende Waren zur Kolonie %s transferiert'),
            $this->getShip()->getName(), $target->getName()));
        foreach ($goods as $key => $value) {
            if ($this->getShip()->getEps() < 1) {
                break;
            }
            if (!array_key_exists($key, $gcount) || $gcount[$key] < 1) {
                continue;
            }
            if (!$this->getShip()->getStorage()->offsetExists($value)) {
                continue;
            }
            $count = $gcount[$key];
            $good = $this->getShip()->getStorage()->offsetGet($value);
            if (!$good->getGood()->isBeamable()) {
                $this->addInformation(sprintf(_('%s ist nicht beambar'), $good->getGood()->getName()));
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
            if ($target->getStorageSum() >= $target->getMaxStorage()) {
                break;
            }
            if ($count > $good->getCount()) {
                $count = $good->getCount();
            }
            if (ceil($count / $good->getGood()->getTransferCount()) > $this->getShip()->getEps()) {
                $count = $this->getShip()->getEps() * $good->getGood()->getTransferCount();
            }
            if ($target->getStorageSum() + $count > $target->getMaxStorage()) {
                $count = $target->getMaxStorage() - $target->getStorageSum();
            }

            $this->addInformation(sprintf(_('%d %s (Energieverbrauch: %d)'), $count, $good->getGood()->getName(),
                ceil($count / $good->getGood()->getTransferCount())));

            $this->getShip()->lowerStorage($value, $count);
            $target->upperStorage($value, $count);
            $this->getShip()->lowerEps(ceil($count / $good->getGood()->getTransferCount()));
            $target->setStorageSum($target->getStorageSum() + $count);
        }
        if ($target->getUserId() != $this->getShip()->getUserId()) {
            $this->sendInformation($target->getUserId(), $this->getShip()->getUserId(), PM_SPECIAL_TRADE);
        }
        $this->getShip()->save();
    }

    function beamFromColony()
    {
        $wrapper = new SystemActivationWrapper($this->getShip());
        if ($wrapper->getError()) {
            $this->addInformation($wrapper->getError());
            return;
        }
        if ($this->getShip()->getEps() == 0) {
            $this->addInformation(_("Keine Energie vorhanden"));
            return;
        }
        if ($this->getShip()->getCloakState()) {
            $this->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($this->getShip()->getWarpState()) {
            $this->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        $target = new Colony(request::postIntFatal('target'));
        if (!$this->preChecks($target, true)) {
            return;
        }
        if (!$this->getShip()->storagePlaceLeft()) {
            $this->addInformation(sprintf(_('Der Lagerraum der %s ist voll'), $this->getShip()->getName()));
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
        $this->addInformation(sprintf(_('Die %s hat folgende Waren von der Kolonie %s transferiert'),
            $this->getShip()->getName(), $target->getName()));
        foreach ($goods as $key => $value) {
            if ($this->getShip()->getEps() < 1) {
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
                $this->addInformation(sprintf(_('%s ist nicht beambar'), $good->getGood()->getName()));
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
            if ($this->getShip()->getStorageSum() >= $this->getShip()->getMaxStorage()) {
                break;
            }
            if ($count > $good->getCount()) {
                $count = $good->getCount();
            }
            if (ceil($count / $good->getGood()->getTransferCount()) > $this->getShip()->getEps()) {
                $count = $this->getShip()->getEps() * $good->getGood()->getTransferCount();
            }
            if ($this->getShip()->getStorageSum() + $count > $this->getShip()->getMaxStorage()) {
                $count = $this->getShip()->getMaxStorage() - $this->getShip()->getStorageSum();
            }

            $this->addInformation(sprintf(_('%d %s (Energieverbrauch: %d)'), $count, $good->getGood()->getName(),
                ceil($count / $good->getGood()->getTransferCount())));

            $target->lowerStorage($value, $count);
            $this->getShip()->upperStorage($value, $count);
            $this->getShip()->lowerEps(ceil($count / $good->getGood()->getTransferCount()));
            $this->getShip()->setStorageSum($this->getShip()->getStorageSum() + $count);
        }
        if ($target->getUserId() != $this->getShip()->getUserId()) {
            $this->sendInformation($target->getUserId(), $this->getShip()->getUserId(), PM_SPECIAL_TRADE);
        }
        $this->getShip()->save();
    }

    function getSelfdestructCode()
    {
        $time = microtime();
        $str = sha1($time * $this->getShip()->getId());
        $str = substr($str, 18, 6);
        $this->session->setSessionVar('sz_code', $str);
        return $str;
    }

    protected function selfDestruct()
    {
        $code = request::postString('destructioncode');
        if ($code != $this->session->getSessionVar('sz_code')) {
            return;
        }
        $this->getShip()->selfDestroy();
        $this->redirectTo('shiplist.php?B_SELFDESTRUCT=1&sstr=' . $this->getSessionString());
    }

    protected function attackShip()
    {
        $targetId = request::postIntFatal('target');
        $target = Ship::getObjectBy('WHERE id=' . $targetId);
        if (!$target) {
            $this->addInformation(_("Ziel kann nicht erfasst werden"));
            return;
        }
        if ($target->getUserId() == currentUser()->getId()) {
            return;
        }
        if (!checkPosition($target, $this->getShip())) {
            $this->addInformation(_("Ziel kann nicht erfasst werden"));
            return;
        }
        if ($target->getRump()->isTrumfield()) {
            return;
        }
        if ($this->getShip()->getEps() == 0) {
            $this->addInformation(_('Keine Energie vorhanden'));
            return;
        }
        if ($this->getShip()->isDisabled()) {
            $this->addInformation(_('Das Schiff ist kampfunfähig'));
            return;
        }
        DB()->beginTransaction();
        if ($this->getShip()->isDocked()) {
            $this->getShip()->setDock(0);
        }
        $fleet = false;
        $target_user_id = $target->getUserId();
        if ($this->getShip()->isFleetLeader()) {
            $attacker = $this->getShip()->getFleet()->getShips();
            $fleet = true;
        } else {
            $attacker = &$this->getShip();
        }
        if ($target->isInFleet()) {
            $defender = $target->getFleet()->getShips();
            $fleet = true;
        } else {
            $defender = &$target;
        }
        $obj = new ShipAttackCycle($attacker, $defender, $this->getShip()->getFleetId(), $target->getFleetId());
        $pm = sprintf(_('Kampf in Sektor %d|%d') . "\n", $this->getShip()->getPosX(), $this->getShip()->getPosY());
        foreach ($obj->getMessages() as $key => $value) {
            $pm .= $value . "\n";
        }
        PM::sendPM(currentUser()->getId(), $target_user_id, $pm, PM_SPECIAL_SHIP);
        DB()->commitTransaction();
        DB()->rollBackTransaction();
        $this->ship = null;
        if ($fleet) {
            $this->addInformation(_("Angriff durchgeführt"));
            $this->fightresult = $obj->getMessages();
        } else {
            $this->addInformationMerge($obj->getMessages());
        }
    }

    protected function interceptShip()
    {
        $target = Ship::getById(request::getIntFatal('target'));
        if (!checkPosition($target, $this->getShip())) {
            return;
        }
        if (!$target->getWarpState()) {
            return;
        }
        if ($target->ownedByCurrentUser()) {
            return;
        }
        if (!$this->getShip()->canIntercept()) {
            return;
        }
        if ($this->getShip()->isDocked()) {
            $this->addInformation('Das Schiff hat abgedockt');
            $this->getShip()->setDock(0);
        }
        if ($target->isInFleet()) {
            $target->getFleet()->deactivateSystem(SYSTEM_WARPDRIVE);
            $this->addInformation("Die Flotte " . $target->getFleet()->getName() . " wurde abgefangen");
            $pm = "Die Flotte " . $target->getFleet()->getName() . " wurde von der " . $this->getShip()->getName() . " abgefangen";
        } else {
            $target->deactivateSystem(SYSTEM_WARPDRIVE);
            $this->addInformation("Die " . $target->getName() . "  wurde abgefangen");
            $pm = "Die " . $target->getName() . " wurde von der " . $this->getShip()->getName() . " abgefangen";
            $target->save();
        }
        PM::sendPM(currentUser()->getId(), $target->getUserId(), $pm, PM_SPECIAL_SHIP);
        if ($this->getShip()->isInFleet()) {
            $this->getShip()->getFleet()->deactivateSystem(SYSTEM_WARPDRIVE);
        } else {
            $this->getShip()->deactivateSystem(SYSTEM_WARPDRIVE);
            $this->getShip()->save();
        }
        // XXX: TBD Red alert
    }

    protected function showDockPrivilegeConfig()
    {
        $this->setPageTitle("Dockrechte");
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/shipmacros.xhtml/dockprivileges');
    }

    protected function addDockPrivilege()
    {
        $target = request::getIntFatal('target');
        $type = request::getIntFatal('type');
        $mode = request::getIntFatal('mode');

        $this->setView('SHOW_DOCKPRIVILEGE_LIST');
        if ($mode != DOCK_PRIVILEGE_MODE_ALLOW && $mode != DOCK_PRIVILEGE_MODE_DENY) {
            return;
        }
        if (count(DockingRights::getBy($this->getShip()->getId(), $target, $type)) != 0) {
            return;
        }
        $save = 0;
        switch ($type) {
            case DOCK_PRIVILEGE_USER:
                if (!User::getUserById($target)) {
                    break;
                }
                $save = 1;
                break;
            case DOCK_PRIVILEGE_ALLIANCE:
                if (!Alliance::getById($target)) {
                    break;
                }
                $save = 1;
                break;
            case DOCK_PRIVILEGE_FACTION:
                if (!Faction::getById($target)) {
                    break;
                }
                $save = 1;
                break;
            default:
                break;
        }
        if ($save == 1) {
            $dock = new DockingRightsData;
            $dock->setPrivilegeMode($mode);
            $dock->setPrivilegeType($type);
            $dock->setTargetId($target);
            $dock->setShipId($this->getShip()->getId());
            $dock->save();
        }
    }

    protected function deleteDockPrivilege()
    {
        $this->setView('SHOW_DOCKPRIVILEGE_LIST');
        $privilegeId = request::getIntFatal('privilegeid');
        $privilege = new DockingRights($privilegeId);
        if ($privilege->getShipId() != $this->getShip()->getId()) {
            return;
        }
        $privilege->deleteFromDatabase();
    }

    protected function showDockPrivilegeList()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/shipmacros.xhtml/dockprivilegelist');
    }

    protected function showTradeMenu()
    {
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/shipmacros.xhtml/trademenu');
        $tradepost = ResourceCache()->getObject('tradepost', request::getIntFatal('postid'));
        if (!checkPosition($this->getShip(), $tradepost->getShip())) {
            new AccessViolation;
        }
        if (!DatabaseUser::checkEntry($tradepost->getShip()->getDatabaseId(), currentUser()->getId())) {
            DatabaseUser::addEntry($tradepost->getShip()->getDatabaseId(), currentUser()->getId());
            $entry = new DatabaseEntry($tradepost->getShip()->getDatabaseId());
            $this->addInformation("Neuer Datenbankeintrag: " . $entry->getDescription());
        }
        $this->setPageTitle('Handelsposten: ' . $tradepost->getName());
        $this->getTemplate()->setVar('TRADEPOST', $tradepost);
    }

    protected function showTradeMenuChoosePayment()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/shipmacros.xhtml/trademenupayment');
        $tradepost = ResourceCache()->getObject('tradepost', request::getIntFatal('postid'));
        if (!checkPosition($this->getShip(), $tradepost->getShip())) {
            new AccessViolation;
        }
        $this->getTemplate()->setVar('TRADEPOST', $tradepost);
    }

    protected function showTradeMenuTransfer()
    {
        $this->setTemplateFile('html/ajaxempty.xhtml');

        $mode = request::getStringFatal('mode');
        switch ($mode) {
            case 'from':
                $this->setAjaxMacro('html/shipmacros.xhtml/transferfromaccount');
                break;
            case 'to':
                $this->setAjaxMacro('html/shipmacros.xhtml/transfertoaccount');
                break;
            default:
                $this->setAjaxMacro('html/shipmacros.xhtml/transfertoaccount');
        }
        $tradepost = ResourceCache()->getObject('tradepost', request::getIntFatal('postid'));
        if (!checkPosition($this->getShip(), $tradepost->getShip())) {
            new AccessViolation;
        }
        $this->getTemplate()->setVar('TRADEPOST', $tradepost);
    }

    /**
     */
    protected function showColonization()
    { #{{{
        $colonyId = request::getIntFatal('colid');
        $colony = new Colony($colonyId);

        $this->setPageTitle(_('Kolonie gründen'));
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/shipmacros.xhtml/colonization');
        $this->getTemplate()->setRef('currentColony', $colony);
    } # }}}

    protected function showRegionInfo()
    {
        $this->setTemplateFile('html/ajaxwindow.xhtml');
        $this->setAjaxMacro('html/shipmacros.xhtml/regioninfo');
        $regionId = request::getIntFatal('region');

        if (!$this->getShip()->getCurrentMapField()->hasRegion()) {
            new AccessViolation;
        }
        if ($this->getShip()->getCurrentMapField()->getMapRegion()->getId() != $regionId) {
            new AccessViolation;
        }
        if ($this->getShip()->getCurrentMapField()->getMapRegion()->getDatabaseId() && !DatabaseUser::checkEntry($this->getShip()->getCurrentMapField()->getMapRegion()->getDatabaseId(),
                currentUser()->getId())) {
            DatabaseUser::addEntry($this->getShip()->getCurrentMapField()->getMapRegion()->getDatabaseId(),
                currentUser()->getId());
            $entry = new DatabaseEntry($this->getShip()->getCurrentMapField()->getMapRegion()->getDatabaseId());
            $this->addInformation("Neuer Datenbankeintrag: " . $entry->getDescription());
        }
        $this->setPageTitle('Details: ' . $this->getShip()->getCurrentMapField()->getMapRegion()->getDescription());
        $this->getTemplate()->setVar('REGION', $this->getShip()->getCurrentMapField()->getMapRegion());
    }

    private function fleetDock($target)
    {
        $msg = array();
        $msg[] = _("Flottenbefehl ausgeführt: Andocken an ") . $target->getName();;
        $freeSlots = $target->getFreeDockingSlotCount();
        foreach ($this->getShip()->getFleet()->getShips() as $key => $ship) {
            if ($freeSlots <= 0) {
                $msg[] = _("Es sind alle Dockplätze belegt");
                break;
            }
            if ($ship->isDocked()) {
                continue;
            }
            if ($ship->getEps() < SYSTEM_ECOST_DOCK) {
                $msg[] = $ship->getName() . _(": Nicht genügend Energie vorhanden");
                continue;
            }
            if ($ship->cloakIsActive()) {
                $msg[] = $ship->getName() . _(': Das Schiff ist getarnt');
                continue;
            }
            $ship->cancelRepair();
            if ($ship->shieldIsActive()) {
                $msg[] = $ship->getName() . _(': Schilde deaktiviert');
                $ship->setShieldState(0);
            }
            $ship->setDock($target->getId());
            $ship->lowerEps(SYSTEM_ECOST_DOCK);
            $ship->save();
            $freeSlots--;
        }
        $this->addInformationMerge($msg);
    }

    private function fleetUnDock()
    {
        $msg = array();
        $msg[] = _("Flottenbefehl ausgeführt: Abdocken von ") . $this->getShip()->getDockedShip()->getName();;
        foreach ($this->getShip()->getFleet()->getShips() as $key => $ship) {
            if (!$ship->isDocked()) {
                continue;
            }
            if ($ship->getEps() < SYSTEM_ECOST_DOCK) {
                $msg[] = $ship->getName() . _(": Nicht genügend Energie vorhanden");
                continue;
            }
            $ship->cancelRepair();
            $ship->setDock(0);
            $ship->lowerEps(SYSTEM_ECOST_DOCK);
            $ship->save();
        }
        $this->addInformationMerge($msg);
    }

    protected function dockShip()
    {
        $targetId = request::getIntFatal('target');
        $target = ResourceCache()->getObject('ship', $targetId);
        if (!$target->isBase()) {
            return;
        }
        if (!checkPosition($this->getShip(), $target)) {
            return;
        }
        if (!DockingRights::checkPrivilegeFor($targetId, currentUser())) {
            $this->addInformation('Das Andocken wurden verweigert');
            return;
        }
        if ($this->getShip()->isFleetLeader()) {
            $this->fleetDock($target);
            return;
        }
        if ($this->getShip()->isDocked()) {
            return;
        }
        if ($this->getShip()->getEps() < SYSTEM_ECOST_DOCK) {
            $this->addInformation('Zum Andocken wird 1 Energie benötigt');
            return;
        }
        if (!$target->hasFreeDockingSlots()) {
            $this->addInformation('Zur Zeit sind alle Dockplätze belegt');
            return;
        }
        if ($this->getShip()->shieldIsActive()) {
            $this->addInformation("Die Schilde wurden deaktiviert");
            $this->getShip()->setShieldState(0);
        }
        if ($this->getShip()->cloakIsActive()) {
            $this->addInformation("Das Schiff ist getarnt");
            return;
        }
        $this->getShip()->cancelRepair();
        $this->getShip()->lowerEps(1);
        $this->getShip()->setDock($targetId);
        $this->getShip()->save();

        PM::sendPm(currentUser()->getId(), $target->getUserId(),
            'Die ' . $this->getShip()->getName() . ' hat an der ' . $target->getName() . ' angedockt', PM_SPECIAL_SHIP);
        $this->addInformation('Andockvorgang abgeschlossen');
    }

    protected function unDockShip()
    {
        if ($this->getShip()->isFleetLeader()) {
            $this->fleetUnDock();
            return;
        }
        if (!$this->getShip()->isDocked()) {
            return;
        }
        if ($this->getShip()->getEps() == 0) {
            $this->addInformation('Zum Abdocken wird 1 Energie benötigt');
            return;
        }
        $this->getShip()->cancelRepair();
        $this->getShip()->lowerEps(1);
        $this->getShip()->setDock(0);
        $this->getShip()->save();

        $this->addInformation('Abdockvorgang abgeschlossen');
    }

    protected function payTradeLicence()
    {
        $this->setView('SHOW_TRADEMENU');
        $tradepost = ResourceCache()->getObject('tradepost', request::getIntFatal('postid'));
        if (!checkPosition($this->getShip(), $tradepost->getShip())) {
            new AccessViolation;
        }
        $targetId = request::getIntFatal('target');
        $mode = request::getStringFatal('method');

        if (!$tradepost->currentUserCanBuyLicence()) {
            return;
        }

        if ($tradepost->currentUserHasLicence()) {
            return;
        }
        switch ($mode) {
            case 'ship':
                $obj = ResourceCache()->getObject('ship', $targetId);
                if (!$obj->ownedByCurrentUser()) {
                    return;
                }
                if (!checkPosition($tradepost->getShip(), $obj)) {
                    return;
                }
                if (!$obj->getStorageByGood($tradepost->getLicenceCostGood()->getId()) || $obj->getStorageByGood($tradepost->getLicenceCostGood()->getId())->getAmount() < $tradepost->calculateLicenceCost()) {
                    return;
                }
                $obj->lowerStorage($tradepost->getLicenceCostGood()->getId(), $tradepost->calculateLicenceCost());
                break;
            case 'account':
                $stor = TradeStorage::getStorageByGood($targetId, currentUser()->getId(),
                    $tradepost->getLicenceCostGood()->getId());
                if ($stor == 0) {
                    return;
                }
                if ($stor->getTradePost()->getTradeNetwork() != $tradepost->getTradeNetwork()) {
                    return;
                }
                $stor->getTradePost()->lowerStorage(currentUser()->getId(), $tradepost->getLicenceCostGood()->getId(),
                    $tradepost->calculateLicenceCost());
                break;
            default:
                return;
        }
        $licence = new TradeLicencesData();
        $licence->setTradePostId($tradepost->getId());
        $licence->setUserId(currentUser()->getId());
        $licence->setDate(time());
        $licence->save();
        $tradepost->userHasLicence = null;
    }

    protected function transferToAccount()
    {
        $wrapper = new SystemActivationWrapper($this->getShip());
        if ($wrapper->getError()) {
            $this->addInformation($wrapper->getError());
            return;
        }
        if ($this->getShip()->getCloakState()) {
            $this->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($this->getShip()->getWarpState()) {
            $this->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        $tradepost = ResourceCache()->getObject('tradepost', request::postIntFatal('postid'));
        if (!checkPosition($this->getShip(), $tradepost->getShip())) {
            return;
        }
        if (!$tradepost->currentUserHasLicence()) {
            return;
        }
        if ($tradepost->getStorageByUser(currentUser()->getId())->getStorageSum() >= $tradepost->getStorage()) {
            $this->addInformation(_('Dein Warenkonto an diesem Posten ist voll'));
            return;
        }
        $goods = request::postArray('goods');
        $gcount = request::postArray('count');
        if ($this->getShip()->getStorage()->count() == 0) {
            $this->addInformation(_("Keine Waren zum Transferieren vorhanden"));
            return;
        }
        if (count($goods) == 0 || count($gcount) == 0) {
            $this->addInformation(_("Es wurde keine Waren zum Transferieren ausgewählt"));
            return;
        }
        $this->addInformation(_("Es wurden folgende Waren ins Warenkonto transferiert"));
        foreach ($goods as $key => $value) {
            if (!array_key_exists($key, $gcount)) {
                continue;
            }
            if (!$this->getShip()->getStorage()->offsetExists($value)) {
                continue;
            }
            $count = $gcount[$key];
            $good = $this->getShip()->getStorage()->offsetGet($value);
            if ($count == "m") {
                $count = $good->getCount();
            } else {
                $count = intval($count);
            }
            if ($count < 1 || $tradepost->getStorage() - $tradepost->getStorageByUser(currentUser()->getId())->getStorageSum() <= 0) {
                continue;
            }
            if (!$good->getGood()->isBeamable()) {
                $this->addInformation(sprintf(_('%s ist nicht beambar'), $good->getGood()->getName()));
                $this->addInformation($good->getGood()->getName() . " ist nicht beambar");
                continue;
            }
            if ($good->getGood()->isIllegal($tradepost->getTradeNetwork())) {
                $this->addInformation(sprintf(_('Der Handel mit %s ist in diesem Handelsnetzwerk verboten'),
                    $good->getGood()->getName()));
                continue;
            }
            if ($count > $good->getAmount()) {
                $count = $good->getAmount();
            }
            if ($tradepost->getStorageByUser(currentUser()->getId())->getStorageSum() + $count > $tradepost->getStorage()) {
                $count = $tradepost->getStorage() - $tradepost->getStorageByUser(currentUser()->getId())->getStorageSum();
            }
            $this->addInformation(sprintf(_('%d %s'), $count, $good->getGood()->getName()));

            $this->getShip()->lowerStorage($value, $count);
            $tradepost->upperStorage(currentUser()->getId(), $value, $count);
            $tradepost->getStorageByUser(currentUser()->getId())->upperSum($count);
        }
    }

    protected function transferFromAccount()
    {
        $wrapper = new SystemActivationWrapper($this->getShip());
        if ($wrapper->getError()) {
            $this->addInformation($wrapper->getError());
            return;
        }
        if ($this->getShip()->getCloakState()) {
            $this->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($this->getShip()->getWarpState()) {
            $this->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        $tradepost = ResourceCache()->getObject('tradepost', request::postIntFatal('postid'));
        if (!checkPosition($this->getShip(), $tradepost->getShip())) {
            return;
        }
        if (!$tradepost->currentUserHasLicence()) {
            return;
        }
        $goods = request::postArray('goods');
        $gcount = request::postArray('count');
        $curGoods = $tradepost->getStorageByUser(currentUser()->getId())->getStorage();
        if (count($curGoods) == 0) {
            $this->addInformation(_("Keine Waren zum Transferieren vorhanden"));
            return;
        }
        if (count($goods) == 0 || count($gcount) == 0) {
            $this->addInformation(_("Es wurde keine Waren zum Transferieren ausgewählt"));
            return;
        }
        $this->addInformation(_("Es wurden folgende Waren vom Warenkonto transferiert"));
        foreach ($goods as $key => $value) {
            if (!array_key_exists($key, $gcount)) {
                continue;
            }
            if (!array_key_exists($value, $curGoods)) {
                continue;
            }
            $count = $gcount[$key];
            if ($count == "m") {
                $count = $curGoods[$value]->getCount();
            } else {
                $count = intval($count);
            }
            if ($count < 1 || $this->getShip()->getStorageSum() >= $this->getShip()->getMaxStorage()) {
                continue;
            }
            if (!$curGoods[$value]->getGood()->isBeamable()) {
                $this->addInformation($curGoods[$value]->getGood()->getName() . " ist nicht beambar");
                continue;
            }
            if ($curGoods[$value]->getGood()->isIllegal($tradepost->getTradeNetwork())) {
                $this->addInformation($curGoods[$value]->getGood()->getName() . ' ist in diesem Handelsnetzwerk illegal und kann nicht gehandelt werden');
                continue;
            }
            if ($count > $curGoods[$value]->getAmount()) {
                $count = $curGoods[$value]->getAmount();
            }
            if ($this->getShip()->getStorageSum() + $count > $this->getShip()->getMaxStorage()) {
                $count = $this->getShip()->getMaxStorage() - $this->getShip()->getStorageSum();
            }
            $tradepost->lowerStorage(currentUser()->getId(), $value, $count);
            $this->getShip()->upperStorage($value, $count);
            $this->getShip()->setStorageSum($this->getShip()->getStorageSum() + $count);

            $this->addInformation($count . " " . $curGoods[$value]->getGood()->getName());
        }
    }

    protected function hideFleet()
    {
        $fleetId = request::getIntFatal('fleet');
        $this->session->storeSessionData('hiddenfleets', $fleetId);
        $this->showNoop();
    }

    protected function showFleet()
    {
        $this->setView('SHOW_NOOP');
        $fleetId = request::getIntFatal('fleet');
        $this->session->deleteSessionData('hiddenfleets', $fleetId);
        $this->showNoop();
    }

    protected function fleetLoadWarpcore($count)
    {
        $msg = array();
        $msg[] = _('Flottenbefehl ausgeführt: Aufladung des Warpkerns');
        DB()->beginTransaction();
        foreach ($this->getShip()->getFleet()->getShips() as $key => $ship) {
            if ($ship->getWarpcoreLoad() >= $ship->getWarpcoreCapacity()) {
                continue;
            }
            $load = $ship->loadWarpCore(!$count ? 1 : ceil(($ship->getWarpcoreCapacity() - $ship->getWarpcoreLoad()) / WARPCORE_LOAD));
            if (!$load) {
                $this->addInformation(sprintf(_('%s: Zum Aufladen des Warpkerns werden mindestens 1 Deuterium sowie 1 Antimaterie benötigt'),
                    $ship->getName()));
                continue;
            }
            $this->addInformation(sprintf(_('%s: Der Warpkern wurde um %d Einheiten aufgeladen'), $ship->getName(),
                $load));
        }
        DB()->commitTransaction();
        $this->addInformationMerge($msg);
    }

    /**
     */
    protected function loadWarpcoreMax()
    { #{{{
        $this->loadWarpcore(INDICATOR_MAX);
    } # }}}


    protected function loadWarpcore($count = false)
    {
        if (request::postString('fleet')) {
            $this->fleetLoadWarpcore($count);
            return;
        }
        if ($this->getShip()->getWarpcoreLoad() >= $this->getShip()->getWarpcoreCapacity()) {
            $this->addInformation(_('Der Warpkern ist bereits vollständig geladen'));
            return;
        }
        DB()->beginTransaction();
        $load = $this->getShip()->loadWarpCore(!$count ? 1 : ceil(($this->getShip()->getWarpcoreCapacity() - $this->getShip()->getWarpcoreLoad()) / WARPCORE_LOAD));
        if (!$load) {
            $this->addInformation(_('Zum Aufladen des Warpkerns werden mindestens 1 Deuterium sowie 1 Antimaterie benötigt'));
            return;
        }
        DB()->commitTransaction();
        $this->addInformation(sprintf(_('Der Warpkern wurde um %d Einheiten aufgeladen'), $load));
    }

    /**
     */
    protected function escapeTraktorBeam()
    { #{{{
        $this->addInformation("Nicht implementiert");
    } # }}}

    /**
     */
    protected function colonize()
    { #{{{
        $colonyId = request::getIntFatal('colid');
        $fieldId = request::getIntFatal('field');
        $colony = new Colony($colonyId);
        $field = new ColFields($fieldId);

        if (!$this->canColonizeCurrentColony()) {
            return;
        }
        DB()->beginTransaction();
        if ($colony->getId() != $field->getColonyId()) {
            return;
        }
        if (!checkColonyPosition($colony, $this->getShip())) {
            return;
        }
        if ($colony->getPlanetType()->getIsMoon()) {
            if ($this->getMoonColonyCount() >= $this->getMoonColonyLimit()) {
                $this->addInformation(_('Es können keine weiteren Monde besiedeln werden'));
                return;
            }
        } else {
            if ($this->getPlanetColonyCount() >= $this->getPlanetColonyLimit()) {
                $this->addInformation(_('Es können keine weiteren Planeten besiedeln werden'));
                return;
            }
        }
        $this->checkDatabaseItem($colony->getPlanetType()->getDatabaseId());
        $base_building = RumpColonizeBuilding::getByRump($this->getShip()->getRump()->getId());
        $colony->colonize(new Building($base_building->getBuildingId()), $field);
        $this->getShip()->deactivateTraktorBeam();
        $this->getShip()->changeFleetLeader();
        $this->getShip()->remove();

        DB()->commitTransaction();

        header('Location: colony.php?id=' . $colony->getId());
        exit;
    } # }}}

    private $fightresult = false;

    /**
     */
    public function hasFightResults()
    { #{{{
        return $this->fightresult !== false;
    } # }}}

    /**
     */
    public function getFightResults()
    { #{{{
        return $this->fightresult;
    } # }}}

    /**
     */
    public function canColonizeCurrentColony()
    { #{{{
        $colony = $this->getShip()->getCurrentColony();
        if ($colony->getPlanetType()->getResearchId() > 0 && !currentUser()->hasResearched($colony->getPlanetType()->getResearchId())) {
            return false;
        }
        return $this->getShip()->getCurrentColony()->isFree() && $this->getShip()->getRump()->canColonize();
    } # }}}

    /**
     */
    protected function renameCrew()
    { #{{{
        $this->setView('SHOW_RENAME_CREW');
        $crew_id = request::getIntFatal('crewid');
        $crew = ResourceCache()->getObject('crew', $crew_id);
        $name = request::getString('rn_crew_' . $crew->getId() . '_value');
        if ($crew->ownedByCurrentUser() && strlen(trim($name)) > 0) {
            $crew->setName(tidyString($name));
            $crew->save();
        }
        $this->getTemplate()->setVar('CREW', $crew);
    } # }}}

    /**
     */
    protected function showRenameCrew()
    { #{{{
        $this->setTemplateFile('html/ajaxempty.xhtml');
        $this->setAjaxMacro('html/shipmacros.xhtml/crewslot');
    } # }}}

    /**
     */
    protected function salvageEpods()
    { #{{{
        $target = ResourceCache()->getObject(CACHE_SHIP, request::postIntFatal('target'));
        $this->preChecks($target);
        if ($target->getCrew() == 0) {
            $this->addInformation(_('Keine Rettungskapseln vorhanden'));
            return;
        }
        if ($this->getShip()->getEps() < 1) {
            $this->addInformation(sprintf(_('Zum Bergen der Rettungskapseln wird %d Energie benötigt'), 1));
            return;
        }
        $this->getShip()->cancelRepair();
        $dummy_crew = current($target->getCrewList());
        if ($dummy_crew->getCrew()->getUserId() != currentUser()->getId()) {
            PM::sendPm(currentUser()->getId(), $dummy_crew->getCrew()->getUserId(),
                sprintf(_('Der Siedler hat %d deiner Crewmitglieder von einem Trümmerfeld geborgen.'),
                    $target->getCrew()), PM_SPECIAL_SHIP);
        }
        ShipCrew::truncate('WHERE ships_id=' . $target->getId());
        $this->getShip()->lowerEps(1);
        $this->getShip()->save();
        $this->addInformation(_('Die Rettungskapseln wurden geborgen'));
    } # }}}

}
