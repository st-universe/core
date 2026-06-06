<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\AlertDetection;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Logging\LogTypeEnum;
use Stu\Module\Logging\StuLogger;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\AlertedBattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyFactoryInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\IncomingBattleParty;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCycleInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;

final class AlertReactionFacade implements AlertReactionFacadeInterface
{
    public function __construct(
        private PrivateMessageSenderInterface $privateMessageSender,
        private SpacecraftAttackCycleInterface $spacecraftAttackCycle,
        private BattlePartyFactoryInterface $battlePartyFactory,
        private AlertDetectionInterface $alertDetection,
    ) {}

    #[\Override]
    public function doItAll(
        SpacecraftWrapperInterface $incomingWrapper,
        InformationInterface $informations,
        ?Spacecraft $tractoringSpacecraft = null,
    ): void {

        $incomingShip = $incomingWrapper->get();
        $startTime = microtime(true);
        StuLogger::log(sprintf(
            'Alert reaction started: incoming=%d incomingUser=%d sector=%s tractoring=%s',
            $incomingShip->getId(),
            $incomingShip->getUser()->getId(),
            $incomingShip->getSectorString(),
            $tractoringSpacecraft?->getId() ?? 'none'
        ), LogTypeEnum::BATTLE);

        $incomingBattleParty = $this->battlePartyFactory->createIncomingBattleParty($incomingWrapper);
        if ($incomingBattleParty->getActiveMembers()->isEmpty()) {
            StuLogger::log(sprintf(
                'Alert reaction finished: incoming=%d result=no-active-members seconds=%F',
                $incomingShip->getId(),
                microtime(true) - $startTime
            ), LogTypeEnum::BATTLE);
            return;
        }

        $alertedBattleParties = $this->alertDetection->detectAlertedBattleParties($incomingShip, $informations, $tractoringSpacecraft);
        if ($alertedBattleParties === []) {
            StuLogger::log(sprintf(
                'Alert reaction finished: incoming=%d result=no-alerted-parties seconds=%F',
                $incomingShip->getId(),
                microtime(true) - $startTime
            ), LogTypeEnum::BATTLE);
            return;
        }

        StuLogger::log(sprintf(
            'Alert reaction detected parties: incoming=%d count=%d seconds=%F',
            $incomingShip->getId(),
            count($alertedBattleParties),
            microtime(true) - $startTime
        ), LogTypeEnum::BATTLE);

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

        StuLogger::log(sprintf(
            'Alert reaction finished: incoming=%d result=done seconds=%F',
            $incomingShip->getId(),
            microtime(true) - $startTime
        ), LogTypeEnum::BATTLE);
    }

    #[\Override]
    public function performAttackCycle(
        AlertedBattlePartyInterface $alertedParty,
        IncomingBattleParty $incomingParty,
        InformationInterface $informations
    ): void {
        $alertUserId = $alertedParty->getUser()->getId();
        $incomingUserId = $incomingParty->getUser()->getId();

        $alertLeader = $alertedParty->getLeader()->get();
        $incomingLeader = $incomingParty->getLeader()->get();
        $cycleStartTime = microtime(true);
        $completed = false;

        StuLogger::log(sprintf(
            'Attack cycle started: cause=%s alertUser=%d incomingUser=%d alertLeader=%d incomingLeader=%d sector=%s alertedActive=%d incomingActive=%d',
            $alertedParty->getAttackCause()->name,
            $alertUserId,
            $incomingUserId,
            $alertLeader->getId(),
            $incomingLeader->getId(),
            $incomingLeader->getSectorString(),
            $alertedParty->getActiveMembers()->count(),
            $incomingParty->getActiveMembers()->count()
        ), LogTypeEnum::BATTLE);

        try {
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

            $completed = true;

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
        } finally {
            StuLogger::log(sprintf(
                'Attack cycle %s: cause=%s alertUser=%d incomingUser=%d alertLeader=%d incomingLeader=%d seconds=%F',
                $completed ? 'finished' : 'aborted',
                $alertedParty->getAttackCause()->name,
                $alertUserId,
                $incomingUserId,
                $alertLeader->getId(),
                $incomingLeader->getId(),
                microtime(true) - $cycleStartTime
            ), LogTypeEnum::BATTLE);
        }
    }
}
