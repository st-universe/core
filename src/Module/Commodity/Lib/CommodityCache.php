<?php

declare(strict_types=1);

namespace Stu\Module\Commodity\Lib;

use Override;
use Stu\Orm\Entity\Commodity;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class CommodityCache implements CommodityCacheInterface
{
    /**
     * @var array<int, Commodity>|null
     */
    private ?array $commodityArray = null;

    public function __construct(private CommodityRepositoryInterface $commodityRepository)
    {
    }

    #[Override]
    public function get(int $commodityId): Commodity
    {
        if ($this->commodityArray === null) {
            $this->commodityArray = $this->commodityRepository->getAll();
        }

        return $this->commodityArray[$commodityId];
    }

    #[Override]
    public function getAll(?int $type = null): array
    {
        if ($this->commodityArray === null) {
            $this->commodityArray = $this->commodityRepository->getAll();
        }

        if ($type !== null) {
            return array_filter($this->commodityArray, fn (Commodity $commodity): bool => $commodity->getType() === $type);
        }

        return $this->commodityArray;
    }
}
