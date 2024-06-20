<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Ship\Lib\Battle\ShipAttackCoreInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\Interaction\InterceptShipCoreInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

class PirateAttack implements PirateAttackInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private InterceptShipCoreInterface $interceptShipCore,
        private ShipAttackCoreInterface $shipAttackCore,
        private ShipWrapperFactoryInterface $shipWrapperFactory,
        private ActivatorDeactivatorHelperInterface $helper,
        private AlertReactionFacadeInterface $alertReactionFacade,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    public function attackShip(FleetWrapperInterface $fleetWrapper, ShipInterface $target): void
    {
        $leadWrapper = $fleetWrapper->getLeadWrapper();

        $this->interceptIfNeccessary($leadWrapper->get(), $target);

        if ($fleetWrapper->get()->getShips()->isEmpty()) {
            $this->logger->log('    cancel attack, no ships left');
            return;
        }

        $this->unwarpIfNeccessary($leadWrapper);

        if ($fleetWrapper->get()->getShips()->isEmpty()) {
            $this->logger->log('    cancel attack, no ships left');
            return;
        }

        $isFleetFight = false;
        $informations = new InformationWrapper();

        $this->logger->logf('    attacking target with shipId: %d', $target->getId());

        $this->shipAttackCore->attack(
            $leadWrapper,
            $this->shipWrapperFactory->wrapShip($target),
            $isFleetFight,
            $informations
        );
    }


    private function interceptIfNeccessary(ShipInterface $ship, ShipInterface $target): void
    {
        //TODO what about tractored ships?
        if (!$target->getWarpDriveState()) {
            return;
        }

        $this->logger->logf('    intercepting target with shipId: %d', $target->getId());
        $this->interceptShipCore->intercept($ship, $target, new InformationWrapper());
    }

    private function unwarpIfNeccessary(ShipWrapperInterface $wrapper): void
    {
        if (!$wrapper->get()->getWarpDriveState()) {
            return;
        }

        $informationWrapper = new InformationWrapper();

        if ($this->helper->deactivateFleet(
            $wrapper,
            ShipSystemTypeEnum::SYSTEM_WARPDRIVE,
            $informationWrapper
        )) {
            $this->logger->log('    deactivated warp');
            $this->alertReactionFacade->doItAll($wrapper, $informationWrapper);
        }
    }
}
