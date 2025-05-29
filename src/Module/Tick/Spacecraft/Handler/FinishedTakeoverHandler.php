<?php

namespace Stu\Module\Tick\Spacecraft\Handler;

use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class FinishedTakeoverHandler implements SpacecraftTickHandlerInterface
{
    public function __construct(
        private ShipTakeoverManagerInterface $shipTakeoverManager
    ) {}

    #[Override]
    public function handleSpacecraftTick(
        SpacecraftWrapperInterface $wrapper,
        InformationInterface $information
    ): void {


        $takeover = $wrapper->get()->getTakeoverActive();
        if ($takeover === null) {
            return;
        }

        $isTakeoverReady = $this->shipTakeoverManager->isTakeoverReady($takeover);
        if ($isTakeoverReady) {
            $this->shipTakeoverManager->finishTakeover($takeover);
        }
    }
}
