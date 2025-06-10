<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\AlertedBattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\IncomingBattleParty;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCycleInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftInterface;

final class AlertReactionFacade implements AlertReactionFacadeInterface
{
    public function __construct(
        private PrivateMessageSenderInterface $privateMessageSender,
        private SpacecraftAttackCycleInterface $spacecraftAttackCycle,
        private BattlePartyFactoryInterface $battlePartyFactory,
        private AlertDetectionInterface $alertDetection,
    ) {}

    #[Override]
    public function doItAll(
        SpacecraftWrapperInterface $incomingWrapper,
        InformationInterface $informations,
        ?SpacecraftInterface $tractoringSpacecraft = null,
    ): void {

        $incomingBattleParty = $this->battlePartyFactory->createIncomingBattleParty($incomingWrapper);
        if ($incomingBattleParty->getActiveMembers()->isEmpty()) {
            return;
        }

        $incomingShip = $incomingWrapper->get();
        $alertedBattleParties = $this->alertDetection->detectAlertedBattleParties($incomingShip, $informations, $tractoringSpacecraft);
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

    #[Override]
    public function performAttackCycle(
        AlertedBattlePartyInterface $alertedParty,
        IncomingBattleParty $incomingParty,
        InformationInterface $informations
    ): void {
        $alertUserId = $alertedParty->getUser()->getId();
        $incomingUserId = $incomingParty->getUser()->getId();

        $alertLeader = $alertedParty->getLeader()->get();
        $incomingLeader = $incomingParty->getLeader()->get();

        $messageCollection = $this->spacecraftAttackCycle->cycle(
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
        $this->privateMessageSender->send(
            $incomingUserId,
            $alertUserId,
            $pm,
            $alertedParty->isStation() ? PrivateMessageFolderTypeEnum::SPECIAL_STATION : PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            $alertLeader->getCondition()->isDestroyed() ? null : $alertLeader->getHref()
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

        if ($incomingLeader->getCondition()->isDestroyed()) {
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
