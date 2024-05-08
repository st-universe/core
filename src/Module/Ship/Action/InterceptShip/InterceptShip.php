<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\InterceptShip;

use Doctrine\ORM\EntityManagerInterface;
use request;
use Stu\Component\Ship\System\Exception\AlreadyOffException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Battle\AlertRedHelperInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class InterceptShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_INTERCEPT';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private PrivateMessageSenderInterface $privateMessageSender,
        private ShipSystemManagerInterface $shipSystemManager,
        private InteractionCheckerInterface $interactionChecker,
        private AlertRedHelperInterface $alertRedHelper,
        private PirateReactionInterface $pirateReaction,
        private ShipWrapperFactoryInterface $shipWrapperFactory,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $shipId = request::indInt('id');
        $targetId = request::indInt('target');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $wrappers->getSource();
        $ship = $wrapper->get();

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

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
        if ($ship->getDockedTo() !== null) {
            $game->addInformation('Das Schiff hat abgedockt');
            $ship->setDockedTo(null);
        }
        if ($target->getFleet() !== null) {
            foreach ($target->getFleet()->getShips() as $fleetShip) {
                try {
                    $this->shipSystemManager->deactivate($this->shipWrapperFactory->wrapShip($fleetShip), ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
                } catch (AlreadyOffException $e) {
                }
                $this->shipLoader->save($fleetShip);
            }

            $game->addInformation("Die Flotte " . $target->getFleet()->getName() . " wurde abgefangen");
            $pm = "Die Flotte " . $target->getFleet()->getName() . " wurde von der " . $ship->getName() . " abgefangen";
        } else {
            $this->shipSystemManager->deactivate($targetWrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);

            $game->addInformation("Die " . $target->getName() . "  wurde abgefangen");
            $pm = "Die " . $target->getName() . " wurde von der " . $ship->getName() . " abgefangen";

            $this->shipLoader->save($target);
        }

        $href = sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $target->getId());

        $this->privateMessageSender->send(
            $userId,
            $target->getUser()->getId(),
            $pm,
            PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            $href
        );
        $interceptorLeftWarp = false;
        if ($ship->getFleet() !== null) {
            foreach ($ship->getFleet()->getShips() as $fleetShip) {
                try {
                    $this->shipSystemManager->deactivate($this->shipWrapperFactory->wrapShip($fleetShip), ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
                    $interceptorLeftWarp = true;
                } catch (AlreadyOffException $e) {
                }
                $this->shipLoader->save($fleetShip);
            }
        } else {
            try {
                $this->shipSystemManager->deactivate($wrapper, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
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

        $this->pirateReaction->checkForPirateReaction(
            $target,
            PirateReactionTriggerEnum::ON_INTERCEPTION,
            $ship
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
