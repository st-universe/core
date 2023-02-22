<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Stu\Module\Building\Action\BuildingActionHandlerInterface;
use Stu\Module\Building\Action\BuildingFunctionActionMapperInterface;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;

/**
 * Handles certain post de-/activation actions depending on building functions
 */
final class BuildingPostAction implements BuildingPostActionInterface
{
    private BuildingFunctionActionMapperInterface $buildingFunctionActionMapper;

    public function __construct(
        BuildingFunctionActionMapperInterface $buildingFunctionActionMapper
    ) {
        $this->buildingFunctionActionMapper = $buildingFunctionActionMapper;
    }

    public function handleDeactivation(
        BuildingInterface $building,
        ColonyInterface $colony
    ): void {
        $this->handle(
            $building,
            static function (BuildingActionHandlerInterface $handler, int $buildingFunctionId) use ($colony): void {
                $handler->deactivate($buildingFunctionId, $colony);
            }
        );
    }

    public function handleActivation(
        BuildingInterface $building,
        ColonyInterface $colony
    ): void {
        $this->handle(
            $building,
            static function (BuildingActionHandlerInterface $handler, int $buildingFunctionId) use ($colony): void {
                $handler->activate($buildingFunctionId, $colony);
            }
        );
    }

    private function handle(
        BuildingInterface $building,
        callable $callback
    ): void {
        foreach ($building->getFunctions() as $function) {
            $buildingFunctionId = $function->getFunction();

            $handler = $this->buildingFunctionActionMapper->map($buildingFunctionId);
            if ($handler !== null) {
                $callback($handler, $buildingFunctionId);
            }
        }
    }
}
