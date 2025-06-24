<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TrackShip;

use Override;
use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class TrackShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TRACK';

    private const int MAXIMUM_TICKS = 70;

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private ActivatorDeactivatorHelperInterface $helper,
        private InteractionCheckerBuilderFactoryInterface $interactionCheckerBuilderFactory
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $shipId = request::getIntFatal('id');
        $targetId = request::getIntFatal('target');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $wrappers->getSource();
        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }

        $target = $targetWrapper->get();
        $ship = $wrapper->get();

        if (!$this->interactionCheckerBuilderFactory
            ->createInteractionChecker()
            ->setSource($ship)
            ->setTarget($target)
            ->setCheckTypes([
                InteractionCheckType::EXPECT_SOURCE_SUFFICIENT_CREW,
                InteractionCheckType::EXPECT_SOURCE_UNSHIELDED,
                InteractionCheckType::EXPECT_SOURCE_UNCLOAKED,
                InteractionCheckType::EXPECT_SOURCE_UNWARPED,
                InteractionCheckType::EXPECT_TARGET_UNWARPED,
                InteractionCheckType::EXPECT_TARGET_UNCLOAKED,
                InteractionCheckType::EXPECT_TARGET_UNSHIELDED
            ])
            ->check($game)) {
            return;
        }

        $tracker = $wrapper->getTrackerSystemData();
        if ($tracker === null || $tracker->targetId !== null) {
            return;
        }

        $eps = $wrapper->getEpsSystemData();
        if ($eps === null || $eps->getEps() === 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }

        // activate system
        if (!$this->helper->activate($wrapper, SpacecraftSystemTypeEnum::TRACKER, $game)) {
            $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);
            return;
        }

        $tracker->setTarget($target->getId())
            ->setRemainingTicks(self::MAXIMUM_TICKS)
            ->update();

        $game->addInformation(sprintf(_('Die %s ist nun mit einem verborgenen Tracker markiert'), $target->getName()));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
