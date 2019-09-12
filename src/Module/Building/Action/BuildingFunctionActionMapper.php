<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Psr\Container\ContainerInterface;

final class BuildingFunctionActionMapper implements BuildingFunctionActionMapperInterface
{
    private $container;

    public function __construct(
        ContainerInterface $container
    ) {
        $this->container = $container;
    }

    public function map(int $buildingFunctionId): ?BuildingActionHandlerInterface
    {
        $map = [
            BUILDING_FUNCTION_ACADEMY => Academy::class,
            BUILDING_FUNCTION_FIGHTER_SHIPYARD => Shipyard::class,
            BUILDING_FUNCTION_ESCORT_SHIPYARD => Shipyard::class,
            BUILDING_FUNCTION_FRIGATE_SHIPYARD => Shipyard::class,
            BUILDING_FUNCTION_CRUISER_SHIPYARD => Shipyard::class,
            BUILDING_FUNCTION_DESTROYER_SHIPYARD => Shipyard::class,
        ];

        $handler = $map[$buildingFunctionId] ?? null;

        if ($handler === null) {
            return null;
        }
        return $this->container->get($handler);
    }
}