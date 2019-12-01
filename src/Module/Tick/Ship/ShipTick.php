<?php

namespace Stu\Module\Tick\Ship;

use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipTick implements ShipTickInterface
{
    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipRepositoryInterface $shipRepository;

    private array $msg = [];

    private ShipSystemManagerInterface $shipSystemManager;

    public function __construct(
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager
    ) {
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
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

                    $this->shipSystemManager->deactivate($ship, $system->getSystemType());

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
            case ShipSystemTypeEnum::SYSTEM_CLOAK:
                return "Tarnung";
            case ShipSystemTypeEnum::SYSTEM_NBS:
                return "Nahbereichssensoren";
            case ShipSystemTypeEnum::SYSTEM_LSS:
                return "Langstreckensensoren";
            case ShipSystemTypeEnum::SYSTEM_PHASER:
                return "Strahlenwaffe";
            case ShipSystemTypeEnum::SYSTEM_TORPEDO:
                return "Torpedobänke";
            case ShipSystemTypeEnum::SYSTEM_WARPDRIVE:
                return "Warpantrieb";
            case ShipSystemTypeEnum::SYSTEM_EPS:
                return _("Energiesystem");
            case ShipSystemTypeEnum::SYSTEM_IMPULSEDRIVE:
                return _("Impulsantrieb");
            case ShipSystemTypeEnum::SYSTEM_COMPUTER:
                return _('Computer');
            case ShipSystemTypeEnum::SYSTEM_WARPCORE:
                return _('Warpkern');
            case ShipSystemTypeEnum::SYSTEM_SHIELDS:
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

        $this->privateMessageSender->send(GameEnum::USER_NOONE, (int)$ship->getUserId(), $text,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP);

        $this->msg = [];
    }
}
