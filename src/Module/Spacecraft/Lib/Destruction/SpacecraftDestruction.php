<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Destruction;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Destruction\Handler\SpacecraftDestructionHandlerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class SpacecraftDestruction implements SpacecraftDestructionInterface
{
    /**
     * @param array<SpacecraftDestructionHandlerInterface> $destructionHandlers
     */
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private array $destructionHandlers
    ) {}

    #[\Override]
    public function destroy(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        array_walk(
            $this->destructionHandlers,
            function (SpacecraftDestructionHandlerInterface $handler) use (
                $destroyer,
                $destroyedSpacecraftWrapper,
                $cause,
                $informations
            ): void {
                $handler->handleSpacecraftDestruction(
                    $destroyer,
                    $destroyedSpacecraftWrapper,
                    $cause,
                    $informations
                );
            }
        );

        $this->spacecraftRepository->delete($destroyedSpacecraftWrapper->get());
    }
}
