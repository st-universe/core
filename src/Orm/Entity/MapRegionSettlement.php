<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_map_regions_settlement')]
#[Entity]
class MapRegionSettlement
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $region_id;

    #[Column(type: 'integer')]
    private int $faction_id;

    public function getId(): int
    {
        return $this->id;
    }

    public function setRegionId(int $region_id): MapRegionSettlement
    {
        $this->region_id = $region_id;

        return $this;
    }

    public function getRegionId(): int
    {
        return $this->region_id;
    }

    public function setFactionId(int $faction_id): MapRegionSettlement
    {
        $this->faction_id = $faction_id;

        return $this;
    }

    public function getFactionId(): int
    {
        return $this->faction_id;
    }
}
