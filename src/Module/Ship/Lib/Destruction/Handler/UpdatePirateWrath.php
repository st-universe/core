<?php

namespace Stu\Module\Ship\Lib\Destruction\Handler;

use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Pirate\Component\PirateWrathManagerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Destruction\ShipDestroyerInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class UpdatePirateWrath implements ShipDestructionHandlerInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private PirateWrathManagerInterface $pirateWrathManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    #[Override]
    public function handleShipDestruction(
        ?ShipDestroyerInterface $destroyer,
        ShipWrapperInterface $destroyedShipWrapper,
        ShipDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        if ($destroyer === null) {
            return;
        }

        $ship = $destroyedShipWrapper->get();
        $targetPrestige = $ship->getRump()->getPrestige();
        if ($targetPrestige < 1) {
            return;
        }

        $destroyerUser = $destroyer->getUser();
        $userOfDestroyed = $destroyedShipWrapper->get()->getUser();

        if ($destroyerUser->getId() === UserEnum::USER_NPC_KAZON) {
            $this->pirateWrathManager->decreaseWrath($userOfDestroyed, $targetPrestige);
        }

        if ($userOfDestroyed->getId() === UserEnum::USER_NPC_KAZON) {

            $fleet = $ship->getFleet();
            $this->logger->log(sprintf(
                '    %s (%s) of fleet %d got destroyed by %s',
                $ship->getName(),
                $ship->getRump()->getName(),
                $fleet === null ? 0 : $fleet->getId(),
                $destroyer->getName()
            ));

            $this->pirateWrathManager->increaseWrath($destroyerUser, $targetPrestige);
        }
    }
}
