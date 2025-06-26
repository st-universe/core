<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Override;
use RuntimeException;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Pirate\Component\PirateRoundManagerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\PirateLoggerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\UserRepositoryInterface;

class UpdatePirateRoundPrestige implements SpacecraftDestructionHandlerInterface
{
    private PirateLoggerInterface $logger;

    public function __construct(
        private PirateRoundManagerInterface $pirateRoundManager,
        private UserRepositoryInterface $userRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getPirateLogger();
    }

    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        $spacecraft = $destroyedSpacecraftWrapper->get();
        $userOfDestroyed = $spacecraft->getUser();

        if (
            $userOfDestroyed->getId() === UserEnum::USER_NPC_KAZON
            && $spacecraft instanceof Ship
            && $destroyer !== null
        ) {
            $destroyerUser = $this->userRepository->find($destroyer->getUserId());
            if ($destroyerUser === null) {
                throw new RuntimeException('this should not happen');
            }

            if ($destroyerUser->isNpc()) {
                return;
            }

            $targetPrestige = $spacecraft->getRump()->getPrestige();
            if ($targetPrestige < 1) {
                return;
            }

            $fleet = $spacecraft->getFleet();
            $this->logger->log(sprintf(
                '    %s (%s) of fleet %d got destroyed, decreasing pirate round prestige by %d',
                $spacecraft->getName(),
                $spacecraft->getRump()->getName(),
                $fleet === null ? 0 : $fleet->getId(),
                $targetPrestige
            ));

            $this->pirateRoundManager->decreasePrestige($targetPrestige);
            $this->pirateRoundManager->addUserStats($destroyerUser, $targetPrestige);
        }
    }
}
