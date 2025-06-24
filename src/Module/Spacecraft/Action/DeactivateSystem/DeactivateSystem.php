<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\DeactivateSystem;

use Override;
use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class DeactivateSystem implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DEACTIVATE_SYSTEM';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private ActivatorDeactivatorHelperInterface $helper,
        private SpacecraftLoaderInterface $spacecraftLoader,
        private AlertReactionFacadeInterface $alertReactionFacade
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::getIntFatal('id'),
            $game->getUser()->getId()
        );

        $systemType = SpacecraftSystemTypeEnum::getByName(request::getStringFatal('type'));

        $success = $this->helper->deactivate(
            $wrapper,
            $systemType,
            $game
        );

        if ($success && $this->isAlertReactionCheckNeeded($systemType)) {
            $spacecraft = $wrapper->get();
            $traktoredShipWrapper = $wrapper->getTractoredShipWrapper();

            //Alarm-Rot check for ship
            $this->alertReactionFacade->doItAll($wrapper, $game);

            //Alarm-Rot check for traktor ship
            if ($traktoredShipWrapper !== null) {
                $this->alertReactionFacade->doItAll($traktoredShipWrapper, $game, $spacecraft);
            }

            if ($spacecraft->getCondition()->isDestroyed()) {
                return;
            }
        }

        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);
    }

    private function isAlertReactionCheckNeeded(SpacecraftSystemTypeEnum $systemType): bool
    {
        return match ($systemType) {
            SpacecraftSystemTypeEnum::CLOAK,
            SpacecraftSystemTypeEnum::WARPDRIVE => true,
            default => false
        };
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
