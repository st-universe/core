<?php

namespace Stu\Orm\Entity;

/**
 * @Entity @Table(name="stu_map_regions_settlement")
 **/
interface MapRegionSettlementInterface
{
    public function getId(): int;

    public function setRegionId(int $region_id): MapRegionSettlementInterface;

    public function getRegionId(): int;

    public function setFactionId(int $faction_id): MapRegionSettlementInterface;

    public function getFactionId(): int;
}
