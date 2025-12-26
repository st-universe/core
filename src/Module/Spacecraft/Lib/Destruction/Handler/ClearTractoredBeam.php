<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class ClearTractoredBeam implements SpacecraftDestructionHandlerInterface
{
    public function __construct(
        private SpacecraftSystemManagerInterface $spacecraftSystemManager
    ) {}

    #[\Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        $tractoredShipWrapper = $destroyedSpacecraftWrapper->getTractoredShipWrapper();
        if ($tractoredShipWrapper !== null) {

            $this->spacecraftSystemManager->deactivate($destroyedSpacecraftWrapper, SpacecraftSystemTypeEnum::TRACTOR_BEAM, true);
        }
    }
}
