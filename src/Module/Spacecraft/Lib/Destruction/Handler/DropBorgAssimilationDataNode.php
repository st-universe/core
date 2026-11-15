<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Override;
use Stu\Component\Borg\BorgAssimilationDestructionDataNode;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class DropBorgAssimilationDataNode implements SpacecraftDestructionHandlerInterface
{
    public function __construct(private BorgAssimilationDestructionDataNode $dataNodes) {}

    #[Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {
        $ship = $destroyedSpacecraftWrapper->get();
        $commodity = $this->dataNodes->grantForDestruction($ship);
        if ($commodity !== null) {
            $informations->addInformationf('In den Trümmern der %s befindet sich 1 %s', $ship->getName(), $commodity->getName());
        }
    }
}
