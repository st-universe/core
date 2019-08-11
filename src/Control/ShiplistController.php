<?php

namespace Stu\Control;

use AccessViolation;
use CrewData;
use Fleet;
use FleetData;
use request;
use Ship;
use ShipCrewData;
use Stu\Lib\Session;
use Tuple;

final class ShiplistController extends GameController
{

    private $default_tpl = "html/shiplist.xhtml";
    private $singleships = null;

    function __construct(
        Session $session
    )
    {
        parent::__construct($session, $this->default_tpl, "/ Schiffe");
        $this->addCallback("B_NEW_FLEET", "createFleet");
        $this->addCallback("B_DELETE_FLEET", "deleteFleet", true);
        $this->addCallback("B_LEAVE_FLEET", "leaveFleet", true);
        $this->addCallback("B_JOIN_FLEET", "joinFleet");
        $this->addCallback("B_CHANGE_NAME", "renameFleet");
        $this->addCallback("B_SELFDESTRUCT", "selfDestruct", true);
        $this->addCallback("B_NOT_OWNER", "displayNotOwner", true);
        $this->addCallback("B_GET_GALAXY_CLASS", "getGalaxyClass");
        $this->addNavigationPart(new Tuple("shiplist.php", "Schiffe"));
    }

    function hasShips()
    {
        return $this->hasFleets() || $this->hasSingleShips() || $this->hasBases();
    }

    function selfDestruct()
    {
        $this->addInformation("Die Selbstzerstörung wurde durchgeführt");
    }

    private $fleets = null;

    public function getFleets()
    {
        if ($this->fleets === null) {
            $this->fleets = Fleet::getFleetsByUser($this->getUser()->getId());
        }
        return $this->fleets;
    }

    private $bases = null;

    public function getBases()
    {
        if ($this->bases === null) {
            $this->bases = Ship::getObjectsBy("WHERE user_id=" . currentUser()->getId() . " AND fleets_id=0 AND is_base=1 ORDER BY id");
        }
        return $this->bases;
    }

    function hasFleets()
    {
        return count($this->getFleets()) > 0;
    }

    function hasBases()
    {
        return count($this->getBases()) > 0;
    }

    function hasSingleShips()
    {
        return count($this->getSingleShips()) > 0;
    }

    public function getSingleShips()
    {
        if ($this->singleships === null) {
            $this->singleships = Ship::getObjectsBy("WHERE user_id=" . currentUser()->getId() . " AND fleets_id=0 AND is_base=0 ORDER BY id");
        }
        return $this->singleships;
    }

    private $currentShip = null;

    function getShip()
    {
        if ($this->currentShip === null) {
            $this->currentShip = Ship::getById(request::indInt('id'));
            if (!$this->currentShip->ownedByCurrentUser()) {
                new AccessViolation;
            }
        }
        return $this->currentShip;
    }

    function createFleet()
    {
        if ($this->getShip()->isInFleet()) {
            return;
        }
        if ($this->getShip()->isBase()) {
            return;
        }
        $fleet = new FleetData;
        $fleet->setFleetLeader($this->getShip()->getId());
        $fleet->setUserId(currentUser()->getId());
        $fleet->save();
        $this->getShip()->setFleetId($fleet->getId());
        $this->getShip()->setFleet($fleet);
        $this->getShip()->save();
        $this->addInformation("Die Flotte wurde erstellt");
    }

    function deleteFleet()
    {
        if (!$this->getShip()->isInFleet()) {
            return;
        }
        if (!$this->getShip()->isFleetLeader()) {
            return;
        }
        $this->getShip()->getFleet()->deleteFromDb();
        $this->getShip()->unsetFleet();
        $this->getShip()->setFleetId(0);
        $this->getShip()->save();
        $this->addInformation("Die Flotte wurde aufgelöst");
    }

    function joinFleet()
    {
        if ($this->getShip()->isInFleet()) {
            return;
        }
        // TBD zusätzliche checks für flotten
        $fleet = Fleet::getUserFleetById(request::postIntFatal('fleetid'));
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
        $this->addInformation("Die " . $this->getShip()->getName() . " ist der Flotte " . $fleet->getName() . " beigetreten");
    }

    function leaveFleet()
    {
        if (!$this->getShip()->isInFleet()) {
            return;
        }
        if ($this->getShip()->isFleetLeader()) {
            return;
        }
        $this->getShip()->leaveFleet();
        $this->getShip()->save();
        $this->addInformation("Die " . $this->getShip()->getName() . " hat die Flotte verlassen");
    }


    protected function renameFleet()
    {
        $fleet = Fleet::getUserFleetById(request::postIntFatal('fleetid'));
        $fleet->setName(request::postStringFatal('fleetname'));
        $fleet->save();
        $this->addInformation("Der Flottenname wurde geändert");
    }

    protected function getGalaxyClass()
    {
        if (!currentUser()->isAdmin() && Ship::countInstances("WHERE user_id=" . currentUser()->getId()) >= 5) {
            $this->addInformation("Diese Aktion ist auf 5 Mirandas pro Siedler beschränkt");
            return;
        }
        $ship = Ship::copyShip(404);
        $ship->setName('Miranda');
        $ship->setUserId(currentUser()->getId());
        $pre = array('Kathryn', 'Geordi', 'Jean-Luc', 'Deana', 'Beverly', 'William', 'Reginald', 'Miles', 'Wesley');
        $post = array('Janeway', 'Crusher', 'Picard', 'Troi', 'Riker', 'Obrien', 'Barcley', 'La Forge');
        $i = 1;
        while ($i <= 5) {
            $j = 1;
            while ($j <= 2) {
                $crew = new CrewData;
                $crew->setGender(rand(1, 2));
                $crew->setName($pre[array_rand($pre)] . " " . $post[array_rand($post)]);
                $crew->setType($i);
                $crew->setUserId(currentUser()->getId());
                $crew->setRaceId(1);
                $crew->save();
                $sc = new ShipCrewData;
                $sc->setCrewId($crew->getId());
                $sc->setShipId($ship->getId());
                $sc->setSlot($i);
                $sc->save();
                $j++;
            }
            $i++;
        }

        $ship->save();
        $this->addInformation("Jawohl Sir!");
    }

    protected function displayNotOwner()
    {
        $this->addInformation("Du bist nicht Besitzer dieses Schiffes");
    }

    /**
     */
    public function getMaxFleetPoints()
    {
        return POINTS_PER_FLEET;
    }
}
