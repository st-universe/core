<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TrackShip;

use request;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Ship;

final class TrackShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_TRACK';

    private const int MAXIMUM_TICKS = 70;

    public function __construct(
        private readonly ShipLoaderInterface $shipLoader,
        private readonly ActivatorDeactivatorHelperInterface $helper,
        private readonly InteractionCheckerBuilderFactoryInterface $interactionCheckerBuilderFactory
    ) {}

    #[\Override]
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
        if (!$target instanceof Ship) {
            return;
        }

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
            ->check($game->getInfo())) {
            return;
        }

        $tracker = $wrapper->getTrackerSystemData();
        if ($tracker === null || $tracker->targetId !== null) {
            return;
        }

        $eps = $wrapper->getEpsSystemData();
        if ($eps === null || $eps->getEps() === 0) {
            $game->getInfo()->addInformation(_("Keine Energie vorhanden"));
            return;
        }

        // activate system
        if (!$this->helper->activate($wrapper, SpacecraftSystemTypeEnum::TRACKER, $game->getInfo())) {
            $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);
            return;
        }

        $tracker->setTarget($target->getId())
            ->setRemainingTicks(self::MAXIMUM_TICKS)
            ->update();

        $game->getInfo()->addInformationf('Die %s ist nun mit einem verborgenen Tracker markiert', $target->getName());
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
