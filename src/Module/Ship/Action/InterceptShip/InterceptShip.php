<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\InterceptShip;

use request;
use Doctrine\ORM\EntityManagerInterface;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Ship\Lib\InteractionCheckerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Component\Ship\System\Exception\AlreadyOffException;
use Stu\Module\Ship\Lib\AlertRedHelperInterface;

final class InterceptShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_INTERCEPT';

    private ShipLoaderInterface $shipLoader;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ShipSystemManagerInterface $shipSystemManager;

    private InteractionCheckerInterface $interactionChecker;

    private AlertRedHelperInterface $alertRedHelper;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipSystemManagerInterface $shipSystemManager,
        InteractionCheckerInterface $interactionChecker,
        AlertRedHelperInterface $alertRedHelper,
        EntityManagerInterface $entityManager
    ) {
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
        $this->shipSystemManager = $shipSystemManager;
        $this->interactionChecker = $interactionChecker;
        $this->alertRedHelper = $alertRedHelper;
        $this->entityManager = $entityManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $shipId = request::indInt('id');
        $targetId = request::indInt('target');

        $shipArray = $this->shipLoader->getByIdAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $ship = $shipArray[$shipId];
        $target = $shipArray[$targetId];

        if ($target === null) {
            return;
        }
        if (!$this->interactionChecker->checkPosition($target, $ship)) {
            return;
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if (!$target->getWarpState()) {
            return;
        }
        if (!$ship->canIntercept()) {
            return;
        }
        if ($ship->getDockedTo()) {
            $game->addInformation('Das Schiff hat abgedockt');
            $ship->setDockedTo(null);
        }
        if ($target->getFleetId()) {
            foreach ($target->getFleet()->getShips() as $fleetShip) {
                try {
                    $this->shipSystemManager->deactivate($fleetShip, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
                } catch (AlreadyOffException $e) {
                }
                $this->shipLoader->save($fleetShip);
            }

            $game->addInformation("Die Flotte " . $target->getFleet()->getName() . " wurde abgefangen");
            $pm = "Die Flotte " . $target->getFleet()->getName() . " wurde von der " . $ship->getName() . " abgefangen";
        } else {
            $this->shipSystemManager->deactivate($target, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);

            $game->addInformation("Die " . $target->getName() . "  wurde abgefangen");
            $pm = "Die " . $target->getName() . " wurde von der " . $ship->getName() . " abgefangen";

            $this->shipLoader->save($target);
        }

        $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $target->getId());

        $this->privateMessageSender->send(
            $userId,
            (int) $target->getUser()->getId(),
            $pm,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            $href
        );
        $interceptorLeftWarp = false;
        if ($ship->getFleetId()) {
            foreach ($ship->getFleet()->getShips() as $fleetShip) {
                try {
                    $this->shipSystemManager->deactivate($fleetShip, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
                    $interceptorLeftWarp = true;
                } catch (AlreadyOffException $e) {
                }
                $this->shipLoader->save($fleetShip);
            }
        } else {
            try {
                $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
                $interceptorLeftWarp = true;
            } catch (AlreadyOffException $e) {
            }

            $this->shipLoader->save($ship);
        }
        $this->entityManager->flush();

        //Alert red check for the target(s)
        $this->alertRedHelper->doItAll($target, $game);

        //Alert red check for the interceptor(s)
        if ($interceptorLeftWarp) {
            $this->alertRedHelper->doItAll($ship, $game);
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
