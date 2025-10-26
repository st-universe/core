<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\WarpdriveBoost;

use request;
use RuntimeException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class WarpdriveBoost implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_WARPDRIVE_BOOST';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private ActivatorDeactivatorHelperInterface $helper
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $activated = $this->helper->activate($wrapper, SpacecraftSystemTypeEnum::WARPDRIVE_BOOSTER, $game->getInfo());
        if ($activated) {
            $warpdrive = $wrapper->getWarpDriveSystemData();
            if ($warpdrive === null) {
                throw new RuntimeException('this should not happen');
            }

            $maxWarpdrive = $warpdrive->getMaxWarpdrive();
            $currentValue = $warpdrive->getWarpDrive();
            $newValue = min(
                $maxWarpdrive,
                $currentValue + (int)round($maxWarpdrive / 3)
            );
            $warpdrive->setWarpDrive($newValue)->update();

            $game->getInfo()->addInformationf(
                'Der Warpdrive wurde um %d Einheiten aufgeladen',
                $newValue - $currentValue
            );

            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::WARPDRIVE_BOOSTER);
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
