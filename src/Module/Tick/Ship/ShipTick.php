<?php

namespace Stu\Module\Tick\Ship;

use ShipData;
use Stu\Module\Communication\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\ShipSystemInterface;

final class ShipTick implements ShipTickInterface
{
    private $privateMessageSender;

    private $msg = [];

    public function __construct(
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->privateMessageSender = $privateMessageSender;
    }

    public function work(ShipData $ship): void
    {
        if ($ship->getCrewCount() < $ship->getBuildplan()->getCrew()) {
            return;
        }
        $eps = $ship->getEps() + $ship->getEpsProduction();
        if ($ship->getEpsUsage() > $eps) {
            foreach ($ship->getActiveSystems() as $system) {
                //echo "- eps: ".$eps." - usage: ".$ship->getEpsUsage()."\n";
                if ($eps - $ship->getEpsUsage() - $system->getEnergyCosts() < 0) {
                    //echo "-- hit system: ".$system->getDescription()."\n";
                    $cb = $system->getShipCallback();
                    $ship->$cb(0);
                    $ship->lowerEpsUsage($system->getEnergyCosts());
                    $this->msg[] = $this->getSystemDescription($system) . ' deaktiviert wegen Energiemangel';
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

    private function getSystemDescription(ShipSystemInterface $shipSystem): string
    {
        switch ($shipSystem->getSystemType()) {
            case SYSTEM_CLOAK:
                return "Tarnung";
            case SYSTEM_NBS:
                return "Nahbereichssensoren";
            case SYSTEM_LSS:
                return "Langstreckensensoren";
            case SYSTEM_PHASER:
                return "Strahlenwaffe";
            case SYSTEM_TORPEDO:
                return "Torpedobänke";
            case SYSTEM_WARPDRIVE:
                return "Warpantrieb";
            case SYSTEM_EPS:
                return _("Energiesystem");
            case SYSTEM_IMPULSEDRIVE:
                return _("Impulsantrieb");
            case SYSTEM_COMPUTER:
                return _('Computer');
            case SYSTEM_WARPCORE:
                return _('Warpkern');
            case SYSTEM_SHIELDS:
                return _('Schilde');
        }
        return '';
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

        $this->privateMessageSender->send(USER_NOONE, (int)$ship->getUserId(), $text, PM_SPECIAL_SHIP);

        $this->msg = [];
    }
}
