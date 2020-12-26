<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Entity\UserInterface;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\TachyonScanRepository")
 * @Table(
 *     name="stu_tachyon_scan",
 *     indexes={
 *         @Index(name="tachyon_scan_user_idx", columns={"user_id"}),
 *         @Index(name="tachyon_scan_map_idx", columns={"map_id"}),
 *         @Index(name="tachyon_scan_sysmap_idx", columns={"starsystem_map_id"})
 *     }
 * )
 **/
class TachyonScan implements TachyonScanInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") */
    private $user_id = 0;
    
    /** @Column(type="integer") */
    private $scan_time = 0;

    /** @Column(type="integer", nullable=true) * */
    private $map_id;

    /** @Column(type="integer", nullable=true) * */
    private $starsystem_map_id;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * @ManyToOne(targetEntity="Map")
     * @JoinColumn(name="map_id", referencedColumnName="id")
     */
    private $map;
    
    /**
     * @ManyToOne(targetEntity="StarSystemMap")
     * @JoinColumn(name="starsystem_map_id", referencedColumnName="id")
     */
    private $starsystem_map;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): TachyonScanInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getScanTime(): int
    {
        return $this->scan_time;
    }
    public function setScanTime(int $scanTime): TachyonScanInterface
    {
        $this->scan_time = $scanTime;
        return $this;
    }

    public function getMap(): ?MapInterface
    {
        return $this->map;
    }

    public function setMap(?MapInterface $map): TachyonScanInterface
    {
        $this->map = $map;
        return $this;
    }

    public function getStarsystemMap(): ?StarSystemMapInterface
    {
        return $this->starsystem_map;
    }

    public function setStarsystemMap(?StarSystemMapInterface $starsystem_map): TachyonScanInterface
    {
        $this->starsystem_map = $starsystem_map;
        return $this;
    }
}
