<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\DeactivateSystem;

use Override;
use request;
use RuntimeException;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Ship;

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

        $fleetWrapper = request::getInt('isfleet') ? $wrapper->getFleetWrapper() : null;
        $systemType = SpacecraftSystemTypeEnum::getByName(request::getStringFatal('type'));

        if ($fleetWrapper === null) {
            $success = $this->helper->deactivate(
                $wrapper,
                $systemType,
                $game->getInfo()
            );
        } else {
            $success = $this->helper->deactivateFleet(
                $wrapper,
                $systemType,
                $game->getInfo()
            );
        }

        if ($success && $this->isAlertReactionCheckNeeded($systemType)) {
            $spacecraft = $wrapper->get();

            if ($fleetWrapper === null) {
                $traktoredShipWrapper = $wrapper->getTractoredShipWrapper();

                //Alarm-Rot check for ship
                $this->alertReactionFacade->doItAll($wrapper, $game->getInfo());

                //Alarm-Rot check for traktor ship
                if ($traktoredShipWrapper !== null) {
                    $this->alertReactionFacade->doItAll($traktoredShipWrapper, $game->getInfo(), $spacecraft);
                }
            } else {
                $tractoredShips = $this->getTractoredShipWrappers($wrapper);
                //Alarm-Rot check for tractored ships
                foreach ($tractoredShips as [$tractoringShipWrapper, $tractoredShipWrapper]) {
                    $this->alertReactionFacade->doItAll($tractoredShipWrapper, $game->getInfo(), $tractoringShipWrapper);
                }
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

    /** @return array<int, array{0: Ship, 1: SpacecraftWrapperInterface}> */
    private function getTractoredShipWrappers(SpacecraftWrapperInterface $leader): array
    {
        /** @var array<int, array{0: Ship, 1: SpacecraftWrapperInterface}> */
        $result = [];

        $fleetWrapper = $leader->getFleetWrapper();
        if ($fleetWrapper === null) {
            throw new RuntimeException('this should not happen');
        }

        foreach ($fleetWrapper->getShipWrappers() as $wrapper) {

            $tractoredWrapper = $wrapper->getTractoredShipWrapper();
            if ($tractoredWrapper !== null) {
                $result[] = [$wrapper->get(), $tractoredWrapper];
            }
        }

        return $result;
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
