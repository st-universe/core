<?php

namespace Stu\Module\Ship\Lib\Destruction\Handler;

use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Destruction\ShipDestroyerInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class HistoryEntryCreation implements ShipDestructionHandlerInterface
{
    public function __construct(
        private EntryCreatorInterface $entryCreator
    ) {
    }

    #[Override]
    public function handleShipDestruction(
        ?ShipDestroyerInterface $destroyer,
        ShipWrapperInterface $destroyedShipWrapper,
        ShipDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        $ship = $destroyedShipWrapper->get();

        $this->entryCreator->addEntry(
            $cause->getHistoryEntryText($destroyer, $ship),
            $destroyer === null ? UserEnum::USER_NOONE : $destroyer->getUser()->getId(),
            $ship
        );
    }
}
