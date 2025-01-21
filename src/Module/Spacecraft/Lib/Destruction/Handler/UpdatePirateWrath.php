<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Override;
use RuntimeException;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Pirate\Component\PirateWrathManagerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class UpdatePirateWrath implements SpacecraftDestructionHandlerInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PirateWrathManagerInterface $pirateWrathManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    #[Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        if ($destroyer === null) {
            return;
        }

        $spacecraft = $destroyedSpacecraftWrapper->get();
        $targetPrestige = $spacecraft->getRump()->getPrestige();
        if ($targetPrestige < 1) {
            return;
        }

        $destroyerUser = $this->userRepository->find($destroyer->getUserId());
        if ($destroyerUser === null) {
            throw new RuntimeException('this should not happen');
        }

        $userOfDestroyed = $destroyedSpacecraftWrapper->get()->getUser();

        if ($destroyerUser->getId() === UserEnum::USER_NPC_KAZON) {
            $this->pirateWrathManager->decreaseWrath($userOfDestroyed, $targetPrestige);
        }

        if (
            $userOfDestroyed->getId() === UserEnum::USER_NPC_KAZON
            && $spacecraft instanceof ShipInterface
        ) {

            $fleet = $spacecraft->getFleet();
            $this->logger->log(sprintf(
                '    %s (%s) of fleet %d got destroyed by %s',
                $spacecraft->getName(),
                $spacecraft->getRump()->getName(),
                $fleet === null ? 0 : $fleet->getId(),
                $destroyer->getName()
            ));

            $this->pirateWrathManager->increaseWrath($destroyerUser, $targetPrestige);
        }
    }
}
