<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\Shutdown;

use Override;
use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class Shutdown implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SHUTDOWN';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private ActivatorDeactivatorHelperInterface $helper,
        private SpacecraftLoaderInterface $spacecraftLoader,
        private AlertReactionFacadeInterface $alertReactionFacade,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $ship = $wrapper->get();
        $traktoredShipWrapper = $wrapper->getTractoredShipWrapper();

        $triggerAlertRed = $ship->getWarpDriveState() || $ship->isCloaked();

        //deactivate all systems except life support and troop quarters
        foreach ($this->spacecraftSystemManager->getActiveSystems($ship) as $system) {
            if (
                $system->getSystemType() !== SpacecraftSystemTypeEnum::LIFE_SUPPORT &&
                $system->getSystemType() !== SpacecraftSystemTypeEnum::TROOP_QUARTERS
            ) {
                $this->helper->deactivate(request::indInt('id'), $system->getSystemType(), $game);
            }
        }

        //set alert to green
        $wrapper->getComputerSystemDataMandatory()->setAlertStateGreen();

        $game->addInformation(_("Der Energieverbrauch wurde auf ein Minimum reduziert"));

        if ($triggerAlertRed) {
            //Alarm-Rot check for ship
            $this->alertReactionFacade->doItAll($wrapper, $game);

            //Alarm-Rot check for traktor ship
            if ($traktoredShipWrapper !== null) {
                $this->alertReactionFacade->doItAll($traktoredShipWrapper, $game, $ship);
            }

            if ($ship->isDestroyed()) {
                return;
            }
        }

        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
