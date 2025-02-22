<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Override;
use Stu\Module\Building\Action\BuildingActionHandlerInterface;
use Stu\Module\Building\Action\BuildingFunctionActionMapperInterface;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ColonySandboxInterface;

/**
 * Handles certain post de-/activation actions depending on building functions
 */
final class BuildingPostAction implements BuildingPostActionInterface
{
    public function __construct(private BuildingFunctionActionMapperInterface $buildingFunctionActionMapper) {}

    #[Override]
    public function handleDeactivation(
        BuildingInterface $building,
        ColonyInterface|ColonySandboxInterface $host
    ): void {
        $this->handle(
            $building,
            static function (BuildingActionHandlerInterface $handler, BuildingFunctionEnum $buildingFunction) use ($host): void {
                $handler->deactivate($buildingFunction, $host);
            }
        );
    }

    #[Override]
    public function handleActivation(
        BuildingInterface $building,
        ColonyInterface|ColonySandboxInterface $host
    ): void {
        $this->handle(
            $building,
            static function (BuildingActionHandlerInterface $handler, BuildingFunctionEnum $buildingFunction) use ($host): void {
                $handler->activate($buildingFunction, $host);
            }
        );
    }

    private function handle(
        BuildingInterface $building,
        callable $callback
    ): void {
        foreach ($building->getFunctions() as $function) {
            $buildingFunction = $function->getFunction();

            $handler = $this->buildingFunctionActionMapper->map($buildingFunction);
            if ($handler !== null) {
                $callback($handler, $buildingFunction);
            }
        }
    }
}
