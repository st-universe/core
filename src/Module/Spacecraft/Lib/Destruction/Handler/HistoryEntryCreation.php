<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class HistoryEntryCreation implements SpacecraftDestructionHandlerInterface
{
    public function __construct(
        private EntryCreatorInterface $entryCreator
    ) {}

    #[Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        $spacecraft = $destroyedSpacecraftWrapper->get();

        $this->entryCreator->addEntry(
            $cause->getHistoryEntryText($destroyer, $spacecraft),
            $destroyer === null ? UserConstants::USER_NOONE : $destroyer->getUserId(),
            $spacecraft
        );
    }
}
