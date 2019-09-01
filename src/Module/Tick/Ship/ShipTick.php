<?php

namespace Stu\Module\Tick\Ship;

use PM;
use ShipData;

final class ShipTick implements ShipTickInterface
{
    private $msg = [];

    public function work(ShipData $ship): void
    {
        if ($ship->getCrewCount() < $ship->getBuildplan()->getCrew()) {
            return;
        }
        $eps = $ship->getEps() + $ship->getEpsProduction();
        if ($ship->getEpsUsage() > $eps) {
            foreach ($ship->getActiveSystems() as $system) {
                //echo "- eps: ".$eps." - usage: ".$ship->getEpsUsage()."\n";
                if ($eps - $ship->getEpsUsage() - $system->getEpsUsage() < 0) {
                    //echo "-- hit system: ".$system->getDescription()."\n";
                    $cb = $system->getShipCallback();
                    $ship->$cb(0);
                    $ship->lowerEpsUsage($system->getEpsUsage());
                    $this->msg[] = $system->getDescription() . ' deaktiviert wegen Energiemangel';
                }
                if ($ship->getEpsUsage() <= $eps) {
                    break;
                }
            }
        }
        $eps -= $ship->getEpsUsage();
        if ($eps > $ship->getMaxEps()) {
            $eps = $ship->getMaxEps();
        }
        $wkuse = $ship->getEpsUsage() + ($eps - $ship->getEps());
        //echo "--- Generated Id ".$ship->getId()." - eps: ".$eps." - usage: ".$ship->getEpsUsage()." - old eps: ".$ship->getEps()." - wk: ".$wkuse."\n";
        $ship->setEps($eps);
        $ship->lowerWarpcoreLoad($wkuse);

        $ship->save();

        $this->sendMessages($ship);
    }

    private function sendMessages(ShipData $ship): void
    {
        if ($this->msg === []) {
            return;
        }
        $text = "Tickreport der " . $ship->getName() . "\n";
        foreach ($this->msg as $key => $msg) {
            $text .= $msg . "\n";
        }
        PM::sendPM(USER_NOONE, $ship->getUserId(), $text, PM_SPECIAL_SHIP);

        $this->msg = [];
    }
}
