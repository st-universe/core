<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\Ship\Lib\Battle\ShipAttackCoreInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Ship\Lib\Interaction\InterceptShipCoreInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Orm\Entity\ShipInterface;

class PirateAttack implements PirateAttackInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private InterceptShipCoreInterface $interceptShipCore,
        private ShipAttackCoreInterface $shipAttackCore,
        private ShipWrapperFactoryInterface $shipWrapperFactory,
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
}
