<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Override;
use RuntimeException;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class PrestigeGain implements SpacecraftDestructionHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private CreatePrestigeLogInterface $createPrestigeLog,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

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

        $ship = $destroyedSpacecraftWrapper->get();
        $rump = $ship->getRump();
        $amount = $rump->getPrestige();

        // nothing to do
        if ($amount === 0) {
            return;
        }

        // empty escape pods to five times negative prestige
        if ($rump->isEscapePods() && $ship->getCrewCount() === 0) {
            $amount *= 5;
        }

        $description = sprintf(
            '%s%d%s Prestige erhalten für die Zerstörung von: %s',
            $amount < 0 ? '[b][color=red]' : '',
            $amount,
            $amount < 0 ? '[/color][/b]' : '',
            $rump->getName()
        );

        $destroyerUser = $this->userRepository->find($destroyer->getUserId());
        if ($destroyerUser === null) {
            throw new RuntimeException('this should not happen');
        }

        $this->createPrestigeLog->createLog($amount, $description, $destroyerUser, time());

        // system pm only for negative prestige
        if ($amount < 0) {
            $this->sendSystemMessage($description, $destroyerUser->getId());
        }
    }

    private function sendSystemMessage(string $description, int $userId): void
    {
        $this->privateMessageSender->send(
            UserEnum::USER_NOONE,
            $userId,
            $description
        );
    }
}
