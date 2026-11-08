<?php

declare(strict_types=1);

namespace Stu\Component\Research;

use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TechnologyRepositoryInterface;
use UnexpectedValueException;

final class TechnologyMaterials
{
    public function __construct(
        private TechnologyRepositoryInterface $repository,
        private CommodityRepositoryInterface $commodities,
        private StorageManagerInterface $storage
    ) {}

    /** @return array<int, int> */
    public function getCosts(TechnologyRecipe $recipe): array
    {
        $costs = [];
        foreach ($this->repository->getCosts($recipe) as $cost) {
            if ($cost->getAmount() <= 0) {
                throw new UnexpectedValueException('Ungültige Materialmenge im Technologierezept');
            }
            $id = $cost->getCommodity()->getId();
            $costs[$id] = ($costs[$id] ?? 0) + $cost->getAmount();
        }
        return $costs;
    }

    /** @param array<int, int> $costs */
    public function canPay(Colony $colony, array $costs, int $energy): bool
    {
        if ($colony->getChangeable()->getEps() < $energy) {
            return false;
        }
        return array_all($costs, static fn (int $amount, int $id): bool => ($colony->getStorage()->get($id)?->getAmount() ?? 0) >= $amount);
    }

    /** @param array<int, int> $costs */
    public function consume(Colony $colony, array $costs, int $energy, InformationInterface $information): bool
    {
        if (!$this->canPay($colony, $costs, $energy)) {
            $information->addInformation('Die benötigten Waren oder die Energie sind nicht vollständig vorhanden');
            return false;
        }
        foreach ($costs as $id => $amount) {
            $commodity = $this->commodities->find($id) ?? throw new UnexpectedValueException('Technologieware fehlt');
            $this->storage->lowerStorage($colony, $commodity, $amount);
        }
        $colony->getChangeable()->lowerEps($energy);
        return true;
    }
}
