<?php

namespace Stu\Lib\Pirate\Component;

use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\Lib\Battle\Party\PirateFleetBattleParty;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCoreInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InterceptShipCoreInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;

class PirateAttack implements PirateAttackInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private InterceptShipCoreInterface $interceptShipCore,
        private SpacecraftAttackCoreInterface $spacecraftAttackCore,
        private SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private ActivatorDeactivatorHelperInterface $helper,
        private AlertReactionFacadeInterface $alertReactionFacade,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    #[\Override]
    public function attackShip(PirateFleetBattleParty $pirateFleetBattleParty, Ship $target): void
    {
        $leadWrapper = $pirateFleetBattleParty->getLeader();
        $targetWrapper = $this->spacecraftWrapperFactory->wrapShip($target);

        $this->interceptIfNeccessary($leadWrapper, $targetWrapper);

        if ($this->isDefeated($pirateFleetBattleParty)) {
            $this->logger->log('    cancel attack, no ships left');
            return;
        }

        $this->unwarpIfNeccessary($leadWrapper);

        if ($this->isDefeated($pirateFleetBattleParty)) {
            $this->logger->log('    cancel attack, no ships left');
            return;
        }

        $isFleetFight = false;
        $informations = new InformationWrapper();

        $this->logger->logf('    attacking target with shipId: %d', $target->getId());

        $this->spacecraftAttackCore->attack(
            $leadWrapper,
            $targetWrapper,
            false,
            $isFleetFight,
            $informations
        );
    }

    private function isDefeated(PirateFleetBattleParty $pirateFleetBattleParty): bool
    {
        return $pirateFleetBattleParty->isDefeated();
    }

    private function interceptIfNeccessary(SpacecraftWrapperInterface $wrapper, SpacecraftWrapperInterface $targetWrapper): void
    {
        $target = $targetWrapper->get();
        if (!$target->getWarpDriveState()) {
            return;
        }

        $this->logger->logf('    intercepting target with shipId: %d', $target->getId());
        $this->interceptShipCore->intercept($wrapper, $targetWrapper, new InformationWrapper());
    }

    private function unwarpIfNeccessary(SpacecraftWrapperInterface $wrapper): void
    {
        if (!$wrapper->get()->getWarpDriveState()) {
            return;
        }

        $informationWrapper = new InformationWrapper();

        if ($this->helper->deactivateFleet(
            $wrapper,
            SpacecraftSystemTypeEnum::WARPDRIVE,
            $informationWrapper
        )) {
            $this->logger->log('    deactivated warp');
            $this->alertReactionFacade->doItAll($wrapper, $informationWrapper);
        }
    }
}
