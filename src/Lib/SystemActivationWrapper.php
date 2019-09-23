<?php

declare(strict_types=1);

use Stu\Orm\Entity\ShipInterface;

class SystemActivationWrapper
{

    private $ship = null;
    private $errormsg = null;

    function __construct(ShipInterface $ship)
    {
        $this->ship = $ship;
    }

    function getShip()
    {
        return $this->ship;
    }

    function setVar($var, $value)
    {
        $this->$var = $value;
    }

    function doCheck()
    {
        if ($this->eps) {
            if ($this->eps > $this->getShip()->getEps()) {
                if ($this->eps == 1) {
                    $this->errormsg = _("Es wird 1 Energie benötigt");
                } else {
                    $this->errormsg = sprintf(_("Es wird %d Energie benötigt"), $this->eps);
                }
                return;
            }
        }
        if ($this->getShip()->getBuildplan()->getCrew() > 0 && $this->getShip()->getCrewCount() == 0) {
            if ($this->getShip()->getBuildplan()->getCrew() == 1) {
                $this->errormsg = _("Es wird 1 Crewmitglied benötigt");
            } else {
                $this->errormsg = sprintf(_("Es werden %d Crewmitglieder benötigt"),
                    $this->getShip()->getBuildplan()->getCrew());
            }
            return;
        }
        $this->updateFields();
    }

    function updateFields()
    {
        if ($this->eps) {
            $this->getShip()->setEps($this->getShip()->getEps() - $this->eps);
        }
    }

    function getError()
    {
        $this->doCheck();
        return (string) $this->errormsg;
    }

}