<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

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

    /**
     * @OneToOne(targetEntity="Map", inversedBy="wormholeEntry")
     * @JoinColumn(name="map_id", referencedColumnName="id")
     */
    private $map;

    /**
     * @ManyToOne(targetEntity="StarSystem")
     * @JoinColumn(name="system_id", referencedColumnName="id")
     */
    private $starSystem;

    /**
     * @OneToOne(targetEntity="StarSystemMap", inversedBy="wormholeEntry")
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
