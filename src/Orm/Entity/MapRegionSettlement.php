<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;
use Override;

#[Table(name: 'stu_map_regions_settlement')]
#[Entity]
class MapRegionSettlement implements MapRegionSettlementInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $region_id;

    #[Column(type: 'integer')]
    private int $faction_id;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function setRegionId(int $region_id): MapRegionSettlementInterface
    {
        $this->region_id = $region_id;

        return $this;
    }

    #[Override]
    public function getRegionId(): int
    {
        return $this->region_id;
    }

    #[Override]
    public function setFactionId(int $faction_id): MapRegionSettlementInterface
    {
        $this->faction_id = $faction_id;

        return $this;
    }

    #[Override]
    public function getFactionId(): int
    {
        return $this->faction_id;
    }
}
