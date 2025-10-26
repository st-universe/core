<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\StandBy;

use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class StandBy implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_STANDBY';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private ActivatorDeactivatorHelperInterface $helper,
        private SpacecraftLoaderInterface $spacecraftLoader,
        private AlertReactionFacadeInterface $alertReactionFacade,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $spacecraft = $wrapper->get();
        $traktoredShipWrapper = $wrapper->getTractoredShipWrapper();

        $triggerAlertRed = $spacecraft->getWarpDriveState() || $spacecraft->isCloaked();

        //set alert to green
        if ($spacecraft->hasComputer()) {
            $wrapper->getComputerSystemDataMandatory()->setAlertStateGreen()->update();
        }

        //deactivate all systems that can be deactivated
        foreach ($this->spacecraftSystemManager->getActiveSystems($spacecraft) as $system) {
            if ($system->getMode() !== SpacecraftSystemModeEnum::MODE_ALWAYS_ON) {
                $this->helper->deactivate(request::indInt('id'), $system->getSystemType(), $game->getInfo());
            }
        }

        $game->getInfo()->addInformation(_("Der Energieverbrauch wurde auf ein Minimum reduziert"));

        if ($triggerAlertRed) {
            //Alarm-Rot check for ship
            $this->alertReactionFacade->doItAll($wrapper, $game->getInfo());

            //Alarm-Rot check for traktor ship
            if ($traktoredShipWrapper !== null) {
                $this->alertReactionFacade->doItAll($traktoredShipWrapper, $game->getInfo(), $spacecraft);
            }

            if ($spacecraft->getCondition()->isDestroyed()) {
                return;
            }
        }

        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
