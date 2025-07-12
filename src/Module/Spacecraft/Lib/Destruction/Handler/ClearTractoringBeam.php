<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class ClearTractoringBeam implements SpacecraftDestructionHandlerInterface
{
    public function __construct(
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {}

    #[Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        if (!$destroyedSpacecraftWrapper instanceof ShipWrapperInterface) {
            return;
        }

        $tractoringShipWrapper = $destroyedSpacecraftWrapper->getTractoringSpacecraftWrapper();
        if ($tractoringShipWrapper !== null) {
            $tractoringShip = $tractoringShipWrapper->get();
            $this->spacecraftSystemManager->deactivate($tractoringShipWrapper, SpacecraftSystemTypeEnum::TRACTOR_BEAM, true);

            $this->privateMessageSender->send(
                UserConstants::USER_NOONE,
                $tractoringShip->getUser()->getId(),
                sprintf(
                    'Die im Traktorstrahl der %s befindliche %s wurde zerstört',
                    $tractoringShip->getName(),
                    $destroyedSpacecraftWrapper->get()->getName()
                ),
                $tractoringShip->getType()->getMessageFolderType(),
                $tractoringShip
            );
        }
    }
}
