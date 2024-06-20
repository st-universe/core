<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\InterceptShip;

use request;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\Interaction\InterceptShipCoreInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class InterceptShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_INTERCEPT';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private InterceptShipCoreInterface $interceptShipCore,
        private InteractionCheckerInterface $interactionChecker,
        private PirateReactionInterface $pirateReaction
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

        if (!$target->isWarped()) {
            return;
        }
        if (!$ship->canIntercept()) {
            return;
        }

        $this->pirateReaction->checkForPirateReaction(
            $target,
            PirateReactionTriggerEnum::ON_INTERCEPTION_BEFORE,
            $ship
        );

        //check if target still on position
        if (!$this->interactionChecker->checkPosition($target, $ship)) {
            $game->addInformationf('Das Ziel ist geflüchtet');
            return;
        }

        $this->interceptShipCore->intercept($ship, $target, $game);

        $this->pirateReaction->checkForPirateReaction(
            $target,
            PirateReactionTriggerEnum::ON_INTERCEPTION_AFTER,
            $ship
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
