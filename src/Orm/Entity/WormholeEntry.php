<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Component\Map\MapEnum;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\WormholeEntryRepository")
 * @Table(
 *     name="stu_wormhole_entry"
 * )
 **/
class WormholeEntry implements WormholeEntryInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $map_id;

    /** @Column(type="integer") * */
    private $system_id;

    /** @Column(type="integer") * */
    private $system_map_id;

    /** @Column(type="smallint", length=1, nullable=true) */
    private $type = MapEnum::WORMHOLE_ENTRY_TYPE_BOTH;

    /** @Column(type="integer", nullable=true) * */
    private $last_used;

    /** @Column(type="integer", nullable=true) * */
    private $cooldown;

    /**
     * @ManyToOne(targetEntity="Map", inversedBy="wormholeEntries")
     * @JoinColumn(name="map_id", referencedColumnName="id")
     */
    private $map;

    /**
     * @ManyToOne(targetEntity="StarSystem")
     * @JoinColumn(name="system_id", referencedColumnName="id")
     */
    private $starSystem;

    /**
     * @ManyToOne(targetEntity="StarSystemMap", inversedBy="wormholeEntries")
     * @JoinColumn(name="system_map_id", referencedColumnName="id")
     */
    private $systemMap;

    public function getId(): int
    {
        return $this->id;
    }

    public function getMap(): MapInterface
    {
        return $this->map;
    }

    public function getSystem(): StarSystemInterface
    {
        return $this->starSystem;
    }

    public function getSystemMap(): StarSystemMapInterface
    {
        return $this->systemMap;
    }
}
