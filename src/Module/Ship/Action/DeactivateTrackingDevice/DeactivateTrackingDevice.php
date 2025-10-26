<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeactivateTrackingDevice;

use request;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class DeactivateTrackingDevice implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DEACTIVATE_TRACKER';

    public function __construct(private ShipLoaderInterface $shipLoader, private ActivatorDeactivatorHelperInterface $helper) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $tracker = $wrapper->getTrackerSystemData();

        if ($tracker === null || $tracker->targetId === null) {
            return;
        }
        $this->helper->deactivate(request::indInt('id'), SpacecraftSystemTypeEnum::TRACKER, $game->getInfo());
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
