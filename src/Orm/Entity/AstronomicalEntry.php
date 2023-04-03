<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

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
     *
     */
    private int $id;

    /**
     * @Column(type="integer")
     *
     */
    private int $user_id = 0;

    /**
     * @Column(type="smallint", length=1)
     *
     */
    private int $state = 0;

    /**
     * @Column(type="integer", nullable=true)
     *
     */
    private ?int $astro_start_turn;

    /**
     * @Column(type="integer") *
     *
     */
    private int $systems_id;

    /**
     * @Column(type="integer", nullable=true) *
     *
     */
    private ?int $starsystem_map_id_1;

    /**
     * @Column(type="integer", nullable=true) *
     *
     */
    private ?int $starsystem_map_id_2;

    /**
     * @Column(type="integer", nullable=true) *
     *
     */
    private ?int $starsystem_map_id_3;

    /**
     * @Column(type="integer", nullable=true) *
     *
     */
    private ?int $starsystem_map_id_4;

    /**
     * @Column(type="integer", nullable=true) *
     *
     */
    private ?int $starsystem_map_id_5;

    /**
     * @var UserInterface
     *
     * @ManyToOne(targetEntity="User", cascade={"persist"})
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @var StarSystemInterface
     *
     * @ManyToOne(targetEntity="StarSystem")
     * @JoinColumn(name="systems_id", referencedColumnName="id")
     */
    private $starSystem;

    /**
     * @var null|StarSystemMapInterface
     *
     * @ManyToOne(targetEntity="StarSystemMap")
     * @JoinColumn(name="starsystem_map_id_1", referencedColumnName="id")
     */
    private $starsystem_map_1;

    /**
     * @var null|StarSystemMapInterface
     *
     * @ManyToOne(targetEntity="StarSystemMap")
     * @JoinColumn(name="starsystem_map_id_2", referencedColumnName="id")
     */
    private $starsystem_map_2;

    /**
     * @var null|StarSystemMapInterface
     *
     * @ManyToOne(targetEntity="StarSystemMap")
     * @JoinColumn(name="starsystem_map_id_3", referencedColumnName="id")
     */
    private $starsystem_map_3;

    /**
     * @var null|StarSystemMapInterface
     *
     * @ManyToOne(targetEntity="StarSystemMap")
     * @JoinColumn(name="starsystem_map_id_4", referencedColumnName="id")
     */
    private $starsystem_map_4;

    /**
     * @var null|StarSystemMapInterface
     *
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

    public function getAstroStartTurn(): ?int
    {
        return $this->astro_start_turn;
    }

    public function setAstroStartTurn(?int $turn): AstronomicalEntryInterface
    {
        $this->astro_start_turn = $turn;
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
