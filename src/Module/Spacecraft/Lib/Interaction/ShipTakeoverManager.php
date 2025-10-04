<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Interaction;

use Override;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Orm\Entity\BuildplanModule;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\ShipTakeover;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ShipTakeoverRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class ShipTakeoverManager implements ShipTakeoverManagerInterface
{
    public function __construct(
        private ShipTakeoverRepositoryInterface $shipTakeoverRepository,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private StorageRepositoryInterface $storageRepository,
        private CreatePrestigeLogInterface $createPrestigeLog,
        private LeaveFleetInterface $leaveFleet,
        private EntryCreatorInterface $entryCreator,
        private PrivateMessageSenderInterface $privateMessageSender,
        private GameControllerInterface $game
    ) {}

    #[Override]
    public function getPrestigeForBoardingAttempt(Spacecraft $target): int
    {
        return (int)ceil($this->getPrestigeForTakeover($target) / 25);
    }

    #[Override]
    public function getPrestigeForTakeover(Spacecraft $target): int
    {
        $buildplan = $target->getBuildplan();
        if ($buildplan === null) {
            return self::BOARDING_PRESTIGE_PER_TRY;
        }

        return $buildplan->getModules()->reduce(
            fn(int $value, BuildplanModule $buildplanModule): int => $value + $buildplanModule->getModule()->getLevel() * self::BOARDING_PRESTIGE_PER_MODULE_LEVEL,
            self::BOARDING_PRESTIGE_PER_TRY
        );
    }

    #[Override]
    public function startTakeover(Spacecraft $source, Spacecraft $target, int $prestige): void
    {
        $takeover = $this->shipTakeoverRepository->prototype();
        $takeover
            ->setSourceSpacecraft($source)
            ->setTargetSpacecraft($target)
            ->setPrestige($prestige)
            ->setStartTurn($this->game->getCurrentRound()->getTurn());

        $this->shipTakeoverRepository->save($takeover);

        $source->setTakeoverActive($takeover);
        if (!$source->getUser()->isNpc()) {
            $this->createPrestigeLog->createLog(
                -$prestige,
                sprintf(
                    '-%d Prestige abgezogen für den Start der Übernahme der %s von Spieler %s',
                    $prestige,
                    $target->getName(),
                    $target->getUser()->getName()
                ),
                $source->getUser(),
                time()
            );
        }

        $isFleet = false;
        if ($target instanceof Ship) {
            $isFleet = $target->getFleet() !== null;
            if ($isFleet) {
                $this->leaveFleet->leaveFleet($target);
            }
        }

        $this->sendStartPm($takeover, $isFleet);
    }


    private function sendStartPm(ShipTakeover $takeover, bool $leftFleet): void
    {
        $sourceShip = $takeover->getSourceSpacecraft();
        $sourceUser = $sourceShip->getUser();
        $target = $takeover->getTargetSpacecraft();
        $targetUser = $target->getUser();

        $this->privateMessageSender->send(
            $sourceUser->getId(),
            $targetUser->getId(),
            sprintf(
                "Die %s von Spieler %s hat mit der Übernahme der %s begonnen.\n%s\n\nÜbernahme erfolgt in %d Runden.",
                $sourceShip->getName(),
                $sourceUser->getName(),
                $target->getName(),
                $leftFleet ? 'Die Flotte wurde daher verlassen.' : '',
                self::TURNS_TO_TAKEOVER
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            $target
        );
    }

    #[Override]
    public function isTakeoverReady(ShipTakeover $takeover): bool
    {
        $remainingTurns = $takeover->getStartTurn() + self::TURNS_TO_TAKEOVER - $this->game->getCurrentRound()->getTurn();
        if ($remainingTurns <= 0) {
            return true;
        }

        // message to owner of target ship
        $this->sendRemainingPm(
            $takeover,
            $takeover->getSourceSpacecraft()->getUser()->getId(),
            $takeover->getTargetSpacecraft(),
            $remainingTurns
        );

        // message to owner of source ship
        $this->sendRemainingPm(
            $takeover,
            UserConstants::USER_NOONE,
            $takeover->getSourceSpacecraft(),
            $remainingTurns
        );

        return false;
    }

    private function sendRemainingPm(
        ShipTakeover $takeover,
        int $fromId,
        Spacecraft $linked,
        int $remainingTurns
    ): void {
        $this->privateMessageSender->send(
            $fromId,
            $linked->getUser()->getId(),
            sprintf(
                'Die Übernahme der %s durch die %s erfolgt in %d Runde(n).',
                $takeover->getTargetSpacecraft()->getName(),
                $takeover->getSourceSpacecraft()->getName(),
                $remainingTurns
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            $linked
        );
    }

    #[Override]
    public function cancelTakeover(
        ?ShipTakeover $takeover,
        ?string $cause = null,
        bool $force = false
    ): void {

        if ($takeover === null) {
            return;
        }

        if (!$force && $this->isTargetTractoredBySource($takeover)) {
            return;
        }

        // message to owner of target ship
        $this->sendCancelPm(
            $takeover,
            $takeover->getSourceSpacecraft()->getUser()->getId(),
            $takeover->getTargetSpacecraft(),
            $cause
        );

        // message to owner of source ship
        $this->sendCancelPm(
            $takeover,
            UserConstants::USER_NOONE,
            $takeover->getSourceSpacecraft(),
            $cause
        );

        if (!$takeover->getSourceSpacecraft()->getUser()->isNpc()) {
            $this->createPrestigeLog->createLog(
                $takeover->getPrestige(),
                sprintf(
                    '%d Prestige erhalten für Abbruch der Übernahme der %s von Spieler %s',
                    $takeover->getPrestige(),
                    $takeover->getTargetSpacecraft()->getName(),
                    $takeover->getTargetSpacecraft()->getUser()->getName()
                ),
                $takeover->getSourceSpacecraft()->getUser(),
                time()
            );
        }

        $this->removeTakeover($takeover);
    }

    private function isTargetTractoredBySource(ShipTakeover $takeover): bool
    {
        $targetSpacecraft = $takeover->getTargetSpacecraft();
        if (!$targetSpacecraft instanceof Ship) {
            return false;
        }

        return $takeover->getSourceSpacecraft() === $targetSpacecraft->getTractoringSpacecraft();
    }

    #[Override]
    public function cancelBothTakeover(Spacecraft $spacecraft, ?string $passiveCause = null): void
    {
        $this->cancelTakeover(
            $spacecraft->getTakeoverActive()
        );

        $this->cancelTakeover(
            $spacecraft->getTakeoverPassive(),
            $passiveCause
        );
    }

    private function sendCancelPm(
        ShipTakeover $takeover,
        int $fromId,
        Spacecraft $linked,
        ?string $cause
    ): void {

        $this->privateMessageSender->send(
            $fromId,
            $linked->getUser()->getId(),
            sprintf(
                'Die Übernahme der %s wurde abgebrochen%s',
                $takeover->getTargetSpacecraft()->getName(),
                $cause ?? ''
            ),
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            $linked
        );
    }

    #[Override]
    public function finishTakeover(ShipTakeover $takeover): void
    {
        $sourceUser = $takeover->getSourceSpacecraft()->getUser();
        $targetShip = $takeover->getTargetSpacecraft();
        $targetUser = $targetShip->getUser();

        // message to previous owner of target ship
        $this->sendFinishedPm(
            $takeover,
            $sourceUser->getId(),
            $targetUser,
            sprintf(
                'Die %s wurde von Spieler %s übernommen',
                $targetShip->getName(),
                $sourceUser->getName()
            ),
            false
        );

        // message to new owner of target ship
        $this->sendFinishedPm(
            $takeover,
            UserConstants::USER_NOONE,
            $sourceUser,
            sprintf(
                'Die %s von Spieler %s wurde übernommen',
                $targetShip->getName(),
                $targetUser->getName()
            ),
            true
        );

        $this->entryCreator->addEntry(
            sprintf(
                _('Die %s (%s) von Spieler %s wurde in Sektor %s durch %s übernommen'),
                $targetShip->getName(),
                $targetShip->getRump()->getName(),
                $targetUser->getName(),
                $targetShip->getSectorString(),
                $sourceUser->getName()
            ),
            $sourceUser->getId(),
            $targetShip
        );

        $this->changeShipOwner($targetShip, $sourceUser);

        $this->removeTakeover($takeover);
    }

    private function changeShipOwner(Spacecraft $ship, User $user): void
    {
        $ship->setUser($user);
        $this->spacecraftRepository->save($ship);

        // change storage owner
        foreach ($ship->getStorage() as $storage) {

            if ($storage->getCommodity()->isBoundToAccount()) {
                $ship->getStorage()->removeElement($storage);
                $this->storageRepository->delete($storage);
            } else {
                $storage->setUser($user);
                $this->storageRepository->save($storage);
            }
        }

        // change torpedo storage owner
        $torpedoStorage = $ship->getTorpedoStorage();
        if ($torpedoStorage !== null) {
            $torpedoStorage->getStorage()->setUser($user);
            $this->storageRepository->save($torpedoStorage->getStorage());
        }
    }

    private function sendFinishedPm(
        ShipTakeover $takeover,
        int $fromId,
        User $to,
        string $message,
        bool $addHref
    ): void {

        $this->privateMessageSender->send(
            $fromId,
            $to->getId(),
            $message,
            PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
            $addHref ? $takeover->getTargetSpacecraft() : null
        );
    }

    private function removeTakeover(ShipTakeover $takeover): void
    {
        $sourceShip = $takeover->getSourceSpacecraft();
        $sourceShip
            ->setTakeoverActive(null)
            ->getCondition()
            ->setState(SpacecraftStateEnum::NONE);

        $takeover->getTargetSpacecraft()->setTakeoverPassive(null);

        $this->shipTakeoverRepository->delete($takeover);
        $this->spacecraftRepository->save($sourceShip);
    }
}
