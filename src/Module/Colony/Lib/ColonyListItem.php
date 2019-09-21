<?php

// @todo enable strict typing
declare(strict_types=0);

namespace Stu\Module\Colony\Lib;

use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetTypeInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class ColonyListItem implements ColonyListItemInterface
{
    private $commodityRepository;

    private $colony;

    public function __construct(
        CommodityRepositoryInterface $commodityRepository,
        ColonyInterface $colony
    ) {
        $this->colony = $colony;
        $this->commodityRepository = $commodityRepository;
    }

    public function getId(): int
    {
        return $this->colony->getId();
    }

    public function getName(): string
    {
        return $this->colony->getName();
    }

    public function getSystem(): StarSystemInterface
    {
        return $this->colony->getSystem();
    }

    public function getSX(): int
    {
        return $this->colony->getSX();
    }

    public function getSY(): int
    {
        return $this->colony->getSY();
    }

    public function getPopulation(): int
    {
        return $this->colony->getPopulation();
    }

    public function getHousing(): int
    {
        return $this->colony->getMaxBev();
    }

    public function getImmigration(): int
    {
        return $this->colony->getImmigration();
    }

    public function getEps(): int
    {
        return $this->colony->getEps();
    }

    public function getMaxEps(): int
    {
        return $this->colony->getMaxEps();
    }

    public function getEpsProduction(): int
    {
        return $this->colony->getEpsProduction();
    }

    public function getStorageSum(): int
    {
        return $this->colony->getStorageSum();
    }

    public function getMaxStorage(): int
    {
        return $this->colony->getMaxStorage();
    }

    public function getStorage(): array
    {
        return $this->colony->getStorage();
    }

    public function getPlanetType(): PlanetTypeInterface
    {
        return $this->colony->getPlanetType();
    }

    public function getProductionSum(): int {
        return $this->colony->getProductionSum();
    }

    public function getGoodUseView(): array
    {
        $stor = $this->colony->getStorage();
        $prod = $this->colony->getProduction();
        $ret = [];
        foreach ($prod as $commodityId => $productionItem) {
            $proc = $productionItem->getProduction();
            if ($proc >= 0) {
                continue;
            }
            $ret[$commodityId]['good'] = $this->commodityRepository->find((int)$productionItem->getGoodId());
            $ret[$commodityId]['production'] = $productionItem;
            if (!array_key_exists($commodityId, $stor)) {
                $ret[$commodityId]['storage'] = 0;
            } else {
                $ret[$commodityId]['storage'] = $stor[$commodityId]->getAmount();
            }
            $ret[$commodityId]['turnsleft'] = floor($ret[$commodityId]['storage'] / abs($proc));
        }
        return $ret;
    }
}