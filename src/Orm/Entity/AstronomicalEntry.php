<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\AstroEntryRepository")
 * @Table(
 *     name="stu_astro_entry",
 *     indexes={
 *         @Index(name="astro_entry_user_idx", columns={"user_id"}),
 *         @Index(name="astro_entry_star_system_idx", columns={"systems_id"})
 *     }
 * )
 **/
class AstronomicalEntry implements AstronomicalEntryInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") */
    private $user_id = 0;

    /** @Column(type="smallint", length=1) */
    private $state = 0;

    /** @Column(type="integer") * */
    private $systems_id;

    /** @Column(type="integer", nullable=true) * */
    private $starsystem_map_id_1;

    /** @Column(type="integer", nullable=true) * */
    private $starsystem_map_id_2;

    /** @Column(type="integer", nullable=true) * */
    private $starsystem_map_id_3;

    /** @Column(type="integer", nullable=true) * */
    private $starsystem_map_id_4;

    /** @Column(type="integer", nullable=true) * */
    private $starsystem_map_id_5;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ManyToOne(targetEntity="StarSystem")
     * @JoinColumn(name="systems_id", referencedColumnName="id")
     */
    private $starSystem;

    /**
     * @ManyToOne(targetEntity="StarSystemMap")
     * @JoinColumn(name="starsystem_map_id_1", referencedColumnName="id")
     */
    private $starsystem_map_1;
    /**
     * @ManyToOne(targetEntity="StarSystemMap")
     * @JoinColumn(name="starsystem_map_id_2", referencedColumnName="id")
     */
    private $starsystem_map_2;
    /**
     * @ManyToOne(targetEntity="StarSystemMap")
     * @JoinColumn(name="starsystem_map_id_3", referencedColumnName="id")
     */
    private $starsystem_map_3;
    /**
     * @ManyToOne(targetEntity="StarSystemMap")
     * @JoinColumn(name="starsystem_map_id_4", referencedColumnName="id")
     */
    private $starsystem_map_4;
    /**
     * @ManyToOne(targetEntity="StarSystemMap")
     * @JoinColumn(name="starsystem_map_id_5", referencedColumnName="id")
     */
    private $starsystem_map_5;

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

    public function setUser(UserInterface $user): AstronomicalEntryInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getState(): int
    {
        return $this->state;
    }

    public function setState(int $state): AstronomicalEntryInterface
    {
        $this->state = $state;
        return $this;
    }

    public function getSystem(): StarSystemInterface
    {
        return $this->starSystem;
    }

    public function setSystem(StarSystemInterface $starSystem): AstronomicalEntryInterface
    {
        $this->starSystem = $starSystem;
        return $this;
    }

    public function getStarsystemMap1(): ?StarSystemMapInterface
    {
        return $this->starsystem_map_1;
    }

    public function setStarsystemMap1(?StarSystemMapInterface $map): AstronomicalEntryInterface
    {
        $this->starsystem_map_1 = $map;
        return $this;
    }

    public function getStarsystemMap2(): ?StarSystemMapInterface
    {
        return $this->starsystem_map_2;
    }

    public function setStarsystemMap2(?StarSystemMapInterface $map): AstronomicalEntryInterface
    {
        $this->starsystem_map_2 = $map;
        return $this;
    }

    public function getStarsystemMap3(): ?StarSystemMapInterface
    {
        return $this->starsystem_map_3;
    }

    public function setStarsystemMap3(?StarSystemMapInterface $map): AstronomicalEntryInterface
    {
        $this->starsystem_map_3 = $map;
        return $this;
    }

    public function getStarsystemMap4(): ?StarSystemMapInterface
    {
        return $this->starsystem_map_4;
    }

    public function setStarsystemMap4(?StarSystemMapInterface $map): AstronomicalEntryInterface
    {
        $this->starsystem_map_4 = $map;
        return $this;
    }

    public function getStarsystemMap5(): ?StarSystemMapInterface
    {
        return $this->starsystem_map_5;
    }

    public function setStarsystemMap5(?StarSystemMapInterface $map): AstronomicalEntryInterface
    {
        $this->starsystem_map_5 = $map;
        return $this;
    }

    public function isMeasured(): bool
    {
        return $this->starsystem_map_1 == null
            && $this->starsystem_map_2 == null
            && $this->starsystem_map_3 == null
            && $this->starsystem_map_4 == null
            && $this->starsystem_map_5 == null;
    }
}
