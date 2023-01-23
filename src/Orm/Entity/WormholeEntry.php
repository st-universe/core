<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
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
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $map_id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $system_id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $system_map_id;

    /**
     * @Column(type="smallint", length=1)
     *
     * @var int
     */
    private $type = MapEnum::WORMHOLE_ENTRY_TYPE_BOTH;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int
     */
    private $last_used;

    /**
     * @Column(type="integer", nullable=true)
     *
     * @var int
     */
    private $cooldown;

    /**
     * @var MapInterface
     *
     * @ManyToOne(targetEntity="Map", inversedBy="wormholeEntries")
     * @JoinColumn(name="map_id", referencedColumnName="id")
     */
    private $map;

    /**
     * @var StarSystemInterface
     *
     * @ManyToOne(targetEntity="StarSystem")
     * @JoinColumn(name="system_id", referencedColumnName="id")
     */
    private $starSystem;

    /**
     * @var StarSystemMapInterface
     *
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

    public function getType(): int
    {
        return $this->type;
    }

    public function setLastUsed(int $lastUsed): WormholeEntryInterface
    {
        $this->last_used = $lastUsed;

        return $this;
    }

    public function isUsable(): bool
    {
        return $this->last_used === null || $this->cooldown === null
            || $this->last_used < time() - $this->cooldown * 60;
    }
}
