<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;

final class BuildingMassActionConfiguration implements BuildingMassActionConfigurationInterface
{
    public const int CONFIGURATION_EPS = 1;
    public const int CONFIGURATION_SELECTION = 2;
    public const int CONFIGURATION_EPS_PRODUCER = 3;
    public const int CONFIGURATION_INDUSTRY = 4;
    public const int CONFIGURATION_RESIDENTIALS = 5;
    public const int CONFIGURATION_COMMODITY_CONSUMER = 6;
    public const int CONFIGURATION_COMMODITY_PRODUCER = 7;

    public function __construct(private PlanetFieldRepositoryInterface $planetFieldRepository) {}

    #[\Override]
    public function getConfigurations(): array
    {
        return [
            self::CONFIGURATION_EPS => fn (PlanetFieldHostInterface $host): iterable => $this->planetFieldRepository->getEnergyConsumingByHost($host),
            self::CONFIGURATION_SELECTION => function (PlanetFieldHostInterface $host, $selection = null): iterable {
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
            self::CONFIGURATION_EPS_PRODUCER => fn (PlanetFieldHostInterface $host): iterable => $this->planetFieldRepository->getEnergyProducingByHost($host),
            self::CONFIGURATION_INDUSTRY => fn (PlanetFieldHostInterface $host): iterable => $this->planetFieldRepository->getWorkerConsumingByHost($host),
            self::CONFIGURATION_RESIDENTIALS => fn (PlanetFieldHostInterface $host): iterable => $this->planetFieldRepository->getHousingProvidingByHost($host),
            self::CONFIGURATION_COMMODITY_CONSUMER => fn (PlanetFieldHostInterface $host, $selection = null): iterable => $this->planetFieldRepository->getCommodityConsumingByHostAndCommodity(
                $host,
                (int) $selection
            ),
            self::CONFIGURATION_COMMODITY_PRODUCER => fn (PlanetFieldHostInterface $host, $selection = null): iterable => $this->planetFieldRepository->getCommodityProducingByHostAndCommodity(
                $host,
                (int) $selection
            ),
        ];
    }
}
