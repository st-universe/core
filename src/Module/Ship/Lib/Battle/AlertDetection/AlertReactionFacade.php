<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\AlertDetection;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Battle\AlertDetection\AlertDetectionInterface;
use Stu\Module\Ship\Lib\Battle\Party\AlertedBattlePartyInterface;
use Stu\Module\Ship\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Ship\Lib\Battle\Party\IncomingBattleParty;
use Stu\Module\Ship\Lib\Battle\ShipAttackCycleInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

final class AlertReactionFacade implements AlertReactionFacadeInterface
{
    public function __construct(
        private PrivateMessageSenderInterface $privateMessageSender,
        private ShipAttackCycleInterface $shipAttackCycle,
        private BattlePartyFactoryInterface $battlePartyFactory,
        private AlertDetectionInterface $alertDetection,
    ) {
    }

    public function doItAll(
        ShipWrapperInterface $incomingShipWrapper,
        InformationInterface $informations,
        ?ShipInterface $tractoringShip = null,
    ): void {

        $incomingBattleParty = $this->battlePartyFactory->createIncomingBattleParty($incomingShipWrapper);
        if ($incomingBattleParty->getActiveMembers()->isEmpty()) {
            return;
        }

        $incomingShip = $incomingShipWrapper->get();
        $alertedBattleParties = $this->alertDetection->detectAlertedBattleParties($incomingShip, $informations, $tractoringShip);
        if ($alertedBattleParties === []) {
            return;
        }

        shuffle($alertedBattleParties);
        foreach ($alertedBattleParties as $alertedBattleParty) {

            if ($incomingBattleParty->isDefeated()) {
                break;
            }

            if ($alertedBattleParty->getActiveMembers()->isEmpty()) {
                break;
            }

            $this->performAttackCycle(
                $alertedBattleParty,
                $incomingBattleParty,
                $informations
            );
        }
    }

    public function performAttackCycle(
        AlertedBattlePartyInterface $alertedParty,
        IncomingBattleParty $incomingParty,
        InformationInterface $informations
    ): void {
        $alertUserId = $alertedParty->getUser()->getId();
        $incomingUserId = $incomingParty->getUser()->getId();

        $alertLeader = $alertedParty->getLeader()->get();
        $incomingLeader = $incomingParty->getLeader()->get();

        $messageCollection = $this->shipAttackCycle->cycle(
            $alertedParty,
            $incomingParty,
            $alertedParty->getAttackCause()
        );

        $fightInformations = $messageCollection->getInformationDump();

        $pm = sprintf(
            _("Eigene Schiffe auf %s, Kampf in Sektor %s\n%s"),
            $alertedParty->getAlertDescription(),
            $incomingLeader->getSectorString(),
            $fightInformations->getInformationsAsString()
        );
        $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $alertLeader->getId());
        $this->privateMessageSender->send(
            $incomingUserId,
            $alertUserId,
            $pm,
            $alertedParty->isBase() ? PrivateMessageFolderTypeEnum::SPECIAL_STATION : PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            $alertLeader->isDestroyed() ? null : $href
        );
        $pm = sprintf(
            _("Fremde Schiffe auf %s, Kampf in Sektor %s\n%s"),
            $alertedParty->getAlertDescription(),
            $incomingLeader->getSectorString(),
            $fightInformations->getInformationsAsString()
        );
        $this->privateMessageSender->send(
            $alertUserId,
            $incomingUserId,
            $pm,
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP
        );

        if ($incomingLeader->isDestroyed()) {
            $fightInformations->dumpTo($informations);
            return;
        }

        $informations->addInformation(sprintf(
            _('%s fremder Schiffe auf Feld %d|%d, Angriff durchgeführt') . "\n",
            $alertedParty->getAlertDescription(),
            $incomingLeader->getPosX(),
            $incomingLeader->getPosY()
        ));
        $fightInformations->dumpTo($informations);
    }
}
