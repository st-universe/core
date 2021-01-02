<?php

namespace Stu\Module\Tick\Ship;

use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLeaverInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipTick implements ShipTickInterface
{
    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipRepositoryInterface $shipRepository;

    private array $msg = [];

    private ShipSystemManagerInterface $shipSystemManager;
    
    private ShipLeaverInterface $shipLeaver;

    public function __construct(
        PrivateMessageSenderInterface $privateMessageSender,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        ShipLeaverInterface $shipLeaver
    ) {
        $this->privateMessageSender = $privateMessageSender;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->shipLeaver = $shipLeaver;
    }

    public function work(ShipInterface $ship): void
    {
        if ($ship->getCrewCount() < $ship->getBuildplan()->getCrew()) {
            return;
        }
        if ($ship->getCrewCount() > 0 && !$ship->isSystemHealthy(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT))
        {
            $this->msg[] = _('Die Lebenserhaltung ist ausgefallen:');
            $this->msg[] = $this->shipLeaver->leave($ship);
            $this->sendMessages($ship);
            return;
        }
        $eps = $ship->getEps() + $ship->getReactorCapacity();
        if ($ship->getEpsUsage() > $eps) {
            foreach ($ship->getActiveSystems() as $system) {

                $energyConsumption = $this->shipSystemManager->getEnergyConsumption($system->getSystemType());

                if ($energyConsumption < 1)
                {
                    continue;
                }

                //echo "- eps: ".$eps." - usage: ".$ship->getEpsUsage()."\n";
                if ($eps - $ship->getEpsUsage() - $energyConsumption < 0) {
                    //echo "-- hit system: ".$system->getDescription()."\n";

                    $this->shipSystemManager->deactivate($ship, $system->getSystemType(), true);

                    $ship->lowerEpsUsage($energyConsumption);
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
        return ShipSystemTypeEnum::getDescription($shipSystem->getSystemType());
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
