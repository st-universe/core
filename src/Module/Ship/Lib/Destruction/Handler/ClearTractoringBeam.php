<?php

namespace Stu\Module\Ship\Lib\Destruction\Handler;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Destruction\ShipDestroyerInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

class ClearTractoringBeam implements ShipDestructionHandlerInterface
{
    public function __construct(
        private ShipSystemManagerInterface $shipSystemManager,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {
    }

    public function handleShipDestruction(
        ?ShipDestroyerInterface $destroyer,
        ShipWrapperInterface $destroyedShipWrapper,
        ShipDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        $tractoringShipWrapper = $destroyedShipWrapper->getTractoringShipWrapper();
        if ($tractoringShipWrapper !== null) {
            $tractoringShip = $tractoringShipWrapper->get();
            $this->shipSystemManager->deactivate($tractoringShipWrapper, ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, true);

            $href = sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $tractoringShip->getId());

            $this->privateMessageSender->send(
                UserEnum::USER_NOONE,
                $tractoringShip->getUser()->getId(),
                sprintf(
                    'Die im Traktorstrahl der %s befindliche %s wurde zerstört',
                    $tractoringShip->getName(),
                    $destroyedShipWrapper->get()->getName()
                ),
                $tractoringShip->isBase() ? PrivateMessageFolderTypeEnum::SPECIAL_STATION : PrivateMessageFolderTypeEnum::SPECIAL_SHIP,
                $href
            );
        }
    }
}
