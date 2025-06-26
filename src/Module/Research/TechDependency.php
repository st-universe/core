<?php

declare(strict_types=1);

namespace Stu\Module\Research;

use Stu\Orm\Entity\Commodity;

final class TechDependency
{
    public function __construct(private string $name, private Commodity $commodity)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCommodity(): Commodity
    {
        return $this->commodity;
    }
}
