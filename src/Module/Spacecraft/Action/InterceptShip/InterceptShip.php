<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\InterceptShip;

use request;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InterceptShipCoreInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Ship;

final class InterceptShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_INTERCEPT';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private InterceptShipCoreInterface $interceptShipCore,
        private InteractionCheckerInterface $interactionChecker,
        private PirateReactionInterface $pirateReaction
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $shipId = request::indInt('id');
        $targetId = request::indInt('target');

        $wrappers = $this->spacecraftLoader->getWrappersBySourceAndUserAndTarget(
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
        if ($target->getLocation() !== $ship->getLocation()) {
            $game->getInfo()->addInformationf('Das Ziel ist geflüchtet');
            return;
        }

        if ($ship instanceof Ship && $ship->getDockedTo() !== null) {
            $game->getInfo()->addInformation('Das Schiff hat abgedockt');
            $ship->setDockedTo(null);
        }

        $this->interceptShipCore->intercept($wrapper, $targetWrapper, $game->getInfo());

        $this->pirateReaction->checkForPirateReaction(
            $target,
            PirateReactionTriggerEnum::ON_INTERCEPTION_AFTER,
            $ship
        );
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
