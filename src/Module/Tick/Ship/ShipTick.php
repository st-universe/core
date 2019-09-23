<?php

namespace Stu\Module\Tick\Ship;

use Stu\Module\Communication\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Communication\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipTick implements ShipTickInterface
{
    private $privateMessageSender;

    private $shipRepository;

    private $msg = [];

    public function __construct(
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRepository = $shipRepository;
    }

    public function work(ShipInterface $ship): void
    {
        if ($ship->getCrewCount() < $ship->getBuildplan()->getCrew()) {
            return;
        }
        $eps = $ship->getEps() + $ship->getReactorCapacity();
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
        $ship->setWarpcoreLoad($ship->getWarpcoreLoad() - $wkuse);

        $this->shipRepository->save($ship);

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

    private function sendMessages(ShipInterface $ship): void
    {
        if ($this->msg === []) {
            return;
        }
        $text = "Tickreport der " . $ship->getName() . "\n";
        foreach ($this->msg as $key => $msg) {
            $text .= $msg . "\n";
        }

        $this->privateMessageSender->send(USER_NOONE, (int)$ship->getUserId(), $text,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP);

        $this->msg = [];
    }
}
