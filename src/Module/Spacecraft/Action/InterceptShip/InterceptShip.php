<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\InterceptShip;

use request;
use Stu\Component\Spacecraft\System\Exception\SpacecraftSystemException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InterceptShipCoreInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\Ship;

final class InterceptShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_INTERCEPT';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private readonly SpacecraftLoaderInterface $spacecraftLoader,
        private readonly InterceptShipCoreInterface $interceptShipCore,
        private readonly PirateReactionInterface $pirateReaction,
        private readonly InteractionCheckerBuilderFactoryInterface $interactionCheckerBuilderFactory,
        private readonly SpacecraftSystemManagerInterface $spacecraftSystemManager
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

        $information = $game->getInfo();

        if (!$this->interactionCheckerBuilderFactory
            ->createInteractionChecker()
            ->setSource($ship)
            ->setTarget($target)
            ->setCheckTypes([
                InteractionCheckType::EXPECT_SOURCE_ENABLED,
                InteractionCheckType::EXPECT_SOURCE_SUFFICIENT_CREW,
                InteractionCheckType::EXPECT_TARGET_NO_VACATION
            ])
            ->check($information)) {
            return;
        }

        if (!$target->isWarped()) {
            return;
        }
        if (!$ship->canIntercept()) {
            return;
        }

        $sourceCloakWasDeactivated = $this->deactivateSourceCloak($wrapper);

        $this->pirateReaction->checkForPirateReaction(
            $target,
            PirateReactionTriggerEnum::ON_INTERCEPTION_BEFORE,
            $ship
        );

        //check if target still on position
        if ($target->getLocation() !== $ship->getLocation()) {
            if ($sourceCloakWasDeactivated) {
                $this->addSourceCloakDeactivationInformation($ship, $information);
            }
            $information->addInformationf('Das Ziel ist geflüchtet');
            return;
        }

        if ($ship instanceof Ship && $ship->getDockedTo() !== null) {
            $information->addInformation('Das Schiff hat abgedockt');
            $ship->setDockedTo(null);
        }

        $this->interceptShipCore->intercept($wrapper, $targetWrapper, $information, $sourceCloakWasDeactivated);

        $this->pirateReaction->checkForPirateReaction(
            $target,
            PirateReactionTriggerEnum::ON_INTERCEPTION_AFTER,
            $ship
        );
    }

    private function deactivateSourceCloak(SpacecraftWrapperInterface $wrapper): bool
    {
        $ship = $wrapper->get();
        if (!$ship->isCloaked()) {
            return false;
        }

        try {
            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::CLOAK, true);
            return true;
        } catch (SpacecraftSystemException) {
        }

        return false;
    }

    private function addSourceCloakDeactivationInformation(Spacecraft $ship, InformationInterface $information): void
    {
        $information->addInformationf(
            _('%s: System %s deaktiviert'),
            $ship->getName(),
            SpacecraftSystemTypeEnum::CLOAK->getDescription()
        );
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
