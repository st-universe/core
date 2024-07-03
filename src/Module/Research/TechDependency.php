<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Stu\Orm\Entity\CommodityInterface;

final class TechDependency
{
    public function __construct(private string $name, private CommodityInterface $commodity)
    {
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
