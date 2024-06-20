<?php

namespace Stu\Module\Ship\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\Collection;
use RuntimeException;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\ShipInterface;

class TrojanHorseNotifier implements TrojanHorseNotifierInterface
{
    public function __construct(
        private PrivateMessageSenderInterface $privateMessageSender
    ) {
    }

    public function informUsersAboutTrojanHorse(
        ShipInterface $incomingShip,
        ?ShipInterface $tractoringShip,
        Collection $users
    ): void {

        if ($tractoringShip === null) {
            throw new RuntimeException('this should not happen');
        }

        $txt = sprintf(
            _('Die %s von Spieler %s ist in Sektor %s eingeflogen und hat dabei die %s von Spieler %s gezogen'),
            $tractoringShip->getName(),
            $tractoringShip->getUser()->getName(),
            $tractoringShip->getSectorString(),
            $incomingShip->getName(),
            $incomingShip->getUser()->getName()
        );

        foreach ($users as $user) {
            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $user->getId(),
                $txt
            );
        }
    }
}
