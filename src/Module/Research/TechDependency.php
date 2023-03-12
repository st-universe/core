<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Stu\Orm\Entity\CommodityInterface;

final class TechDependency
{
    private string $name;
    private CommodityInterface $commodity;

    public function __construct(
        string $name,
        CommodityInterface $commodity
    ) {
        $this->name = $name;
        $this->commodity = $commodity;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCommodity(): CommodityInterface
    {
        return $this->commodity;
    }
}
