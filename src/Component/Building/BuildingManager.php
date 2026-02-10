<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Stu\Orm\Entity\PlanetField;

/**
 * Manages actions relating to buildings on planets
 */
final class BuildingManager implements BuildingManagerInterface
{
    public function __construct(
        private readonly BuildingActivationHandler $buildingActivationHandler,
        private readonly BuildingRemovalHandler $buildingRemovalHandler,
        private readonly BuildingFinishHandler $buildingFinishHandler
    ) {}

    #[\Override]
    public function activate(PlanetField $field): bool
    {
        return $this->buildingActivationHandler->activate($field);
    }

    #[\Override]
    public function deactivate(PlanetField $field): void
    {
        $this->buildingActivationHandler->deactivate($field);
    }

    #[\Override]
    public function remove(PlanetField $field, bool $isDueToUpgrade = false): void
    {
        $this->buildingRemovalHandler->remove($field, $isDueToUpgrade);
    }

    #[\Override]
    public function finish(PlanetField $field, bool $activate = true): ?string
    {
        return $this->buildingFinishHandler->finish($field, $activate);
    }
}
