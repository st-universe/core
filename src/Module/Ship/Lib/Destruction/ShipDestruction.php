<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Destruction;

use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\Destruction\Handler\ShipDestructionHandlerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShipDestruction implements ShipDestructionInterface
{

    /**
     * @param array<ShipDestructionHandlerInterface> $destructionHandlers
     */
    public function __construct(
        private ShipRepositoryInterface $shipRepository,
        private array $destructionHandlers
    ) {
    }

    #[Override]
    public function destroy(
        ?ShipDestroyerInterface $destroyer,
        ShipWrapperInterface $destroyedShipWrapper,
        ShipDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        array_walk(
            $this->destructionHandlers,
            function (ShipDestructionHandlerInterface $handler) use (
                $destroyer,
                $destroyedShipWrapper,
                $cause,
                $informations
            ): void {
                $handler->handleShipDestruction(
                    $destroyer,
                    $destroyedShipWrapper,
                    $cause,
                    $informations
                );
            }
        );

        $this->shipRepository->save($destroyedShipWrapper->get());
    }
}
