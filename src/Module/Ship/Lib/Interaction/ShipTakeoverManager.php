<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Interaction;

use Stu\Component\Ship\ShipEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipTakeoverInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipTakeoverRepositoryInterface;

final class ShipTakeoverManager implements ShipTakeoverManagerInterface
{
    public const BOARDING_PRESTIGE_PER_TRY = 200;
    public const BOARDING_PRESTIGE_PER_MODULE_LEVEL = 10;

    private ShipTakeoverRepositoryInterface $shipTakeoverRepository;

    private ShipRepositoryInterface $shipRepository;

    private CreatePrestigeLogInterface $createPrestigeLog;

    private PrivateMessageSenderInterface $privateMessageSender;

    private GameControllerInterface $game;

    public function __construct(
        ShipTakeoverRepositoryInterface $shipTakeoverRepository,
        ShipRepositoryInterface $shipRepository,
        CreatePrestigeLogInterface $createPrestigeLog,
        PrivateMessageSenderInterface $privateMessageSender,
        GameControllerInterface $game
    ) {
        $this->shipTakeoverRepository = $shipTakeoverRepository;
        $this->shipRepository = $shipRepository;
        $this->createPrestigeLog = $createPrestigeLog;
        $this->privateMessageSender = $privateMessageSender;
        $this->game = $game;
    }

    public function getPrestigeForBoardingAttempt(ShipInterface $target): int
    {
        return (int)ceil($this->getPrestigeForTakeover($target) / 10);
    }

    public function getPrestigeForTakeover(ShipInterface $target): int
    {
        $buildplan = $target->getBuildplan();
        if ($buildplan === null) {
            return self::BOARDING_PRESTIGE_PER_TRY;
        }

        return array_reduce(
            $buildplan->getModules()->toArray(),
            fn (int $value, BuildplanModuleInterface $buildplanModule) => $value + $buildplanModule->getModule()->getLevel() * self::BOARDING_PRESTIGE_PER_MODULE_LEVEL,
            self::BOARDING_PRESTIGE_PER_TRY
        );
    }

    public function startTakeover(ShipInterface $source, ShipInterface $target, int $prestige): void
    {
        $takeover = $this->shipTakeoverRepository->prototype();
        $takeover
            ->setSourceShip($source)
            ->setTargetShip($target)
            ->setPrestige($prestige)
            ->setStartTurn($this->game->getCurrentRound()->getTurn());

        $this->shipTakeoverRepository->save($takeover);

        $this->createPrestigeLog->createLog(
            -$prestige,
            sprintf(
                '-%d Prestige erhalten für den Start der Übernahme der %s von Spieler %s',
                $prestige,
                $target->getName(),
                $target->getUser()->getName()
            ),
            $source->getUser(),
            time()
        );
    }

    public function isTakeoverReady(ShipTakeoverInterface $takeover): bool
    {
        $remainingTurns = $takeover->getStartTurn() + ShipEnum::TURNS_TO_TAKEOVER - $this->game->getCurrentRound()->getTurn();
        if ($remainingTurns <= 0) {
            return true;
        }

        // message to owner of target ship
        $this->sendRemainingPm(
            $takeover,
            $takeover->getSourceShip()->getUser()->getId(),
            $takeover->getTargetShip(),
            $remainingTurns
        );

        // message to owner of source ship
        $this->sendRemainingPm(
            $takeover,
            UserEnum::USER_NOONE,
            $takeover->getSourceShip(),
            $remainingTurns
        );

        return false;
    }

    private function sendRemainingPm(
        ShipTakeoverInterface $takeover,
        int $fromId,
        ShipInterface $linked,
        int $remainingTurns
    ): void {
        $href = sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $linked->getId());

        $this->privateMessageSender->send(
            $fromId,
            $linked->getUser()->getId(),
            sprintf(
                'Die Übernahme der %s durch die %s erfolgt in %d Runde(n).',
                $takeover->getTargetShip()->getName(),
                $takeover->getSourceShip()->getName(),
                $remainingTurns
            ),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            $href
        );
    }

    public function cancelTakeover(
        ?ShipTakeoverInterface $takeover,
        ?string $cause,
    ): void {

        if ($takeover === null) {
            return;
        }

        // message to owner of target ship
        $this->sendCancelPm(
            $takeover,
            $takeover->getSourceShip()->getUser()->getId(),
            $takeover->getTargetShip(),
            $cause
        );

        // message to owner of source ship
        $this->sendCancelPm(
            $takeover,
            UserEnum::USER_NOONE,
            $takeover->getSourceShip(),
            $cause
        );

        $this->createPrestigeLog->createLog(
            $takeover->getPrestige(),
            sprintf(
                '%d Prestige erhalten für Abbruch der Übernahme der %s von Spieler %s',
                $takeover->getPrestige(),
                $takeover->getTargetShip()->getName(),
                $takeover->getTargetShip()->getUser()->getName()
            ),
            $takeover->getSourceShip()->getUser(),
            time()
        );

        $this->removeTakeover($takeover);
    }

    private function sendCancelPm(
        ShipTakeoverInterface $takeover,
        int $fromId,
        ShipInterface $linked,
        ?string $cause
    ): void {
        $href = sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $linked->getId());

        $this->privateMessageSender->send(
            $fromId,
            $linked->getUser()->getId(),
            sprintf(
                'Die Übernahme der %s wurde abgebrochen%s',
                $takeover->getTargetShip()->getName(),
                $cause ?? ''
            ),
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            $href
        );
    }

    public function finishTakeover(ShipTakeoverInterface $takeover): void
    {
        $sourceUser = $takeover->getSourceShip()->getUser();
        $targetShip = $takeover->getTargetShip();

        // message to previous owner of target ship
        $this->sendFinishedPm(
            $takeover,
            $sourceUser->getId(),
            $targetShip->getUser(),
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
            UserEnum::USER_NOONE,
            $sourceUser,
            sprintf(
                'Die %s von Spieler %s wurde übernommen',
                $targetShip->getName(),
                $targetShip->getUser()->getName()
            ),
            true
        );

        $targetShip->setUser($sourceUser);
        $this->shipRepository->save($targetShip);

        $this->removeTakeover($takeover);
    }

    private function sendFinishedPm(
        ShipTakeoverInterface $takeover,
        int $fromId,
        UserInterface $to,
        string $message,
        bool $addHref
    ): void {
        $href = sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $takeover->getTargetShip()->getId());

        $this->privateMessageSender->send(
            $fromId,
            $to->getId(),
            $message,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            $addHref ? $href : null
        );
    }

    private function removeTakeover(ShipTakeoverInterface $takeover): void
    {
        $takeover->getSourceShip()->unsetTakeover(true);
        $takeover->getTargetShip()->unsetTakeover(false);
        $this->shipTakeoverRepository->delete($takeover);
    }
}
