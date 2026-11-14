<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Override;
use Stu\Component\Borg\BorgAssimilationDestructionNanites;
use Stu\Component\Borg\BorgAssimilationService;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ClearBorgAssimilation implements SpacecraftDestructionHandlerInterface
{
    public function __construct(private BorgAssimilationService $assimilation, private BorgAssimilationDestructionNanites $nanites) {}

    #[Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {
        $this->nanites->grantForDestruction($destroyedSpacecraftWrapper->get());
        $this->assimilation->deleteForSpacecraft($destroyedSpacecraftWrapper->get(), true);
    }
}
