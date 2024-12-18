<?php

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\SpacecraftInterface;

class TrojanHorseNotifier implements TrojanHorseNotifierInterface
{
    public function __construct(
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[Override]
    public function informUsersAboutTrojanHorse(
        SpacecraftInterface $incomingSpacecraft,
        ?SpacecraftInterface $tractoringSpacecraft,
        Collection $users
    ): void {

        if ($tractoringSpacecraft === null) {
            return;
        }

        $txt = sprintf(
            _('Die %s von Spieler %s ist in Sektor %s eingeflogen und hat dabei die %s von Spieler %s gezogen'),
            $tractoringSpacecraft->getName(),
            $tractoringSpacecraft->getUser()->getName(),
            $tractoringSpacecraft->getSectorString(),
            $incomingSpacecraft->getName(),
            $incomingSpacecraft->getUser()->getName()
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
