<?php

declare(strict_types=1);

namespace Stu\Module\Commodity\Lib;

use Override;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class CommodityCache implements CommodityCacheInterface
{
    /**
     * @var array<int, CommodityInterface>|null
     */
    private ?array $commodityArray = null;

    public function __construct(private CommodityRepositoryInterface $commodityRepository)
    {
    }

    #[Override]
    public function get(int $commodityId): CommodityInterface
    {
        if ($this->commodityArray === null) {
            $this->commodityArray = $this->commodityRepository->getAll();
        }

        return $this->commodityArray[$commodityId];
    }

    #[Override]
    public function getAll(int $type = null): array
    {
        if ($this->commodityArray === null) {
            $this->commodityArray = $this->commodityRepository->getAll();
        }

        if ($type !== null) {
            return array_filter($this->commodityArray, fn (CommodityInterface $commodity): bool => $commodity->getType() === $type);
        }

        return $this->commodityArray;
    }
}
