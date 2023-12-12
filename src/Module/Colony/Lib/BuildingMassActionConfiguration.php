<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Lib\Colony\PlanetFieldHostInterface;
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
            static::CONFIGURATION_EPS => fn (PlanetFieldHostInterface $host): iterable => $this->planetFieldRepository->getEnergyConsumingByHost($host),
            static::CONFIGURATION_SELECTION => function (PlanetFieldHostInterface $host, $selection = null): iterable {
                if (!is_array($selection)) {
                    return [];
                }

                $fields = [];
                foreach ($selection as $id) {
                    $planetField = $this->planetFieldRepository->find($id);
                    if ($planetField !== null && $planetField->getHost() === $host) {
                        $fields[] = $planetField;
                    }
                }
                return $fields;
            },
            static::CONFIGURATION_EPS_PRODUCER => fn (PlanetFieldHostInterface $host): iterable => $this->planetFieldRepository->getEnergyProducingByHost($host),
            static::CONFIGURATION_INDUSTRY => fn (PlanetFieldHostInterface $host): iterable => $this->planetFieldRepository->getWorkerConsumingByHost($host),
            static::CONFIGURATION_RESIDENTIALS => fn (PlanetFieldHostInterface $host): iterable => $this->planetFieldRepository->getHousingProvidingByHost($host),
            static::CONFIGURATION_COMMODITY_CONSUMER => fn (PlanetFieldHostInterface $host, $selection = null): iterable => $this->planetFieldRepository->getCommodityConsumingByHostAndCommodity(
                $host,
                (int) $selection
            ),
            static::CONFIGURATION_COMMODITY_PRODUCER => fn (PlanetFieldHostInterface $host, $selection = null): iterable => $this->planetFieldRepository->getCommodityProducingByHostAndCommodity(
                $host,
                (int) $selection
            ),
        ];
    }
}
