<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class BuildingMassActionConfiguration implements BuildingMassActionConfigurationInterface
{
    public const CONFIGURATION_EPS = 1;
    public const CONFIGURATION_SELECTION = 2;
    public const CONFIGURATION_EPS_PRODUCER = 3;
    public const CONFIGURATION_INDUSTRY = 4;
    public const CONFIGURATION_RESIDENTIALS = 5;
    public const CONFIGURATION_COMMODITY_CONSUMER = 6;
    public const CONFIGURATION_COMMODITY_PRODUCER = 7;

    private PlanetFieldRepositoryInterface $planetFieldRepository;

    public function __construct(
        PlanetFieldRepositoryInterface $planetFieldRepository
    ) {
        $this->planetFieldRepository = $planetFieldRepository;
    }

    public function getConfigurations(): array
    {
        return [
            static::CONFIGURATION_EPS => function (ColonyInterface $colony, $selection = null): iterable {
                return $this->planetFieldRepository->getEnergyConsumingByColony($colony->getId());
            },
            static::CONFIGURATION_SELECTION => function (ColonyInterface $colony, $selection = null): iterable {
                if (!is_array($selection)) {
                    return [];
                }
                $colonyId = $colony->getId();

                $fields = [];
                foreach ($selection as $fieldId) {
                    $fields[] = $this->planetFieldRepository->getByColonyAndFieldId($colonyId, (int) $fieldId);
                }
                return $fields;
            },
            static::CONFIGURATION_EPS_PRODUCER => function (ColonyInterface $colony, $selection = null): iterable {
                return $this->planetFieldRepository->getEnergyProducingByColony($colony->getId());
            },
            static::CONFIGURATION_INDUSTRY => function (ColonyInterface $colony, $selection = null): iterable {
                return $this->planetFieldRepository->getWorkerConsumingByColony($colony->getId());
            },
            static::CONFIGURATION_RESIDENTIALS => function (ColonyInterface $colony, $selection = null): iterable {
                return $this->planetFieldRepository->getHousingProvidingByColony($colony->getId());
            },
            static::CONFIGURATION_COMMODITY_CONSUMER => function (ColonyInterface $colony, $selection = null): iterable {
                return $this->planetFieldRepository->getCommodityConsumingByColonyAndCommodity(
                    $colony->getId(),
                    (int) $selection
                );
            },
            static::CONFIGURATION_COMMODITY_PRODUCER => function (ColonyInterface $colony, $selection = null): iterable {
                return $this->planetFieldRepository->getCommodityProducingByColonyAndCommodity(
                    $colony->getId(),
                    (int) $selection
                );
            },
        ];
    }
}
