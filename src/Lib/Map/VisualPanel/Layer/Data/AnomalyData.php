<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Data;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

#[Entity]
class AnomalyData extends AbstractData
{
    public function __construct(
        int $x,
        int $y,
        #[Column(type: 'string', nullable: true)]
        private ?string $anomalytypes = null
    ) {
        parent::__construct($x, $y);
    }

    /** @return null|array<string> */
    public function getAnomalyTypes(): ?array
    {
        if (
            $this->anomalytypes === null
            || $this->anomalytypes === ''
        ) {
            return null;
        }

        return explode(",", $this->anomalytypes);
    }
}
