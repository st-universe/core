<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity
 * @Table(name="stu_map_regions_settlement")
 **/
final class MapRegionSettlement implements MapRegionSettlementInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $region_id;

    /** @Column(type="integer") * */
    private $faction_id;

    public function getId(): int
    {
        return $this->id;
    }

    public function setRegionId(int $region_id): MapRegionSettlementInterface
    {
        $this->region_id = $region_id;

        return $this;
    }

    public function getRegionId(): int
    {
        return $this->region_id;
    }

    public function setFactionId(int $faction_id): MapRegionSettlementInterface
    {
        $this->faction_id = $faction_id;

        return $this;
    }

    public function getFactionId(): int
    {
        return $this->faction_id;
    }
}