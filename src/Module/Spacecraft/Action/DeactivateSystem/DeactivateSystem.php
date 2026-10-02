<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\DeactivateSystem;

use request;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Spacecraft;

final class DeactivateSystem implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DEACTIVATE_SYSTEM';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private ActivatorDeactivatorHelperInterface $helper,
        private SpacecraftLoaderInterface $spacecraftLoader,
        private AlertReactionFacadeInterface $alertReactionFacade
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::getIntFatal('id'),
            $context->getUser()->getId()
        );

        $fleetWrapper = request::getInt('isfleet') ? $wrapper->getFleetWrapper() : null;
        $systemType = SpacecraftSystemTypeEnum::getByName(request::getStringFatal('type'));

        if ($fleetWrapper === null) {
            $success = $this->helper->deactivate(
                $wrapper,
                $systemType,
                $context->getInfo()
            );
        } else {
            $success = $this->helper->deactivateFleet(
                $wrapper,
                $systemType,
                $context->getInfo()
            );
        }

        if ($success && $this->isAlertReactionCheckNeeded($systemType)) {
            $this->triggerAlertReaction($fleetWrapper, $wrapper, $context->getInfo());
            if ($wrapper->get()->getCondition()->isDestroyed()) {
                return;
            }
        }

        $context->setView(ShowSpacecraft::VIEW_IDENTIFIER);
    }

    private function isAlertReactionCheckNeeded(SpacecraftSystemTypeEnum $systemType): bool
    {
        return match ($systemType) {
            SpacecraftSystemTypeEnum::CLOAK,
            SpacecraftSystemTypeEnum::WARPDRIVE => true,
            default => false
        };
    }

    private function triggerAlertReaction(?FleetWrapperInterface $fleetWrapper, SpacecraftWrapperInterface $wrapper, InformationInterface $info): void
    {
        $tractoredShips = $this->getTractoredShipWrappers($fleetWrapper, $wrapper);

        //Alarm-Rot check for ship
        $this->alertReactionFacade->doItAll($wrapper, $info);

        //Alarm-check for tractored ships
        foreach ($tractoredShips as [$tractoringSpacecraftWrapper, $tractoredShipWrapper]) {
            $this->alertReactionFacade->doItAll($tractoredShipWrapper, $info, $tractoringSpacecraftWrapper);
        }
    }

    /** @return array<int, array{0: Spacecraft, 1: ShipWrapperInterface}> */
    private function getTractoredShipWrappers(?FleetWrapperInterface $fleetWrapper, SpacecraftWrapperInterface $wrapper): array
    {
        /** @var array<int, array{0: Spacecraft, 1: ShipWrapperInterface}> */
        $result = [];

        if ($fleetWrapper === null) {
            $traktoredShipWrapper = $wrapper->getTractoredShipWrapper();

            if ($traktoredShipWrapper !== null) {
                $result[] = [$wrapper->get(), $traktoredShipWrapper];
            }

        } else {
            foreach ($fleetWrapper->getShipWrappers() as $wrapper) {

                $tractoredWrapper = $wrapper->getTractoredShipWrapper();
                if ($tractoredWrapper !== null) {
                    $result[] = [$wrapper->get(), $tractoredWrapper];
                }
            }
        }

        return $result;
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
