<?php

declare(strict_types=1);

namespace Stu\Module\Commodity\Lib;

use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;

final class CommodityCache implements CommodityCacheInterface
{
    private CommodityRepositoryInterface $commodityRepository;

    /**
     * @var array<int, CommodityInterface>|null
     */
    private ?array $commodityArray = null;

    public function __construct(
        CommodityRepositoryInterface $commodityRepository
    ) {
        $this->commodityRepository = $commodityRepository;
    }

    public function get(int $commodityId): CommodityInterface
    {
        if ($this->commodityArray === null) {
            $this->commodityArray = $this->commodityRepository->getAll();
        }

        return $this->commodityArray[$commodityId];
    }
}
