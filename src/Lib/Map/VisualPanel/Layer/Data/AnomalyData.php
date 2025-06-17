<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Data;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;

#[Entity]
class AnomalyData extends AbstractData
{
    #[Column(type: 'string', nullable: true)]
    private ?string $anomalytypes = null;

    public function __construct(
        int $x,
        int $y,
        ?string $anomalytypes = null
    ) {
        parent::__construct($x, $y);
        $this->anomalytypes = $anomalytypes;
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
