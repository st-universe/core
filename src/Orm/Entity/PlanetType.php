<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\PlanetTypeRepository")
 * @Table(
 *     name="stu_colonies_classes",
 *     indexes={
 *     }
 * )
 **/
class PlanetType implements PlanetTypeInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="string") */
    private $name = '';

    /** @Column(type="boolean") */
    private $is_moon = false;

    /** @Column(type="integer", nullable=true) * */
    private $research_id;

    /** @Column(type="integer", nullable=true) * */
    private $database_id;

    /** @Column(type="json") */
    private $colonizeable_fields = [];

    /** @Column(type="smallint") * */
    private $bev_growth_rate = 0;

    /** @Column(type="smallint") * */
    private $special = 0;

    /** @Column(type="boolean") */
    private $allow_start = false;

    /**
     * @OneToOne(targetEntity="DatabaseEntry")
     * @JoinColumn(name="database_id", referencedColumnName="id")
     */
    private $databaseEntry;

    /**
     * @ManyToOne(targetEntity="Research")
     * @JoinColumn(name="research_id", referencedColumnName="id")
     */
    private $research;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): PlanetTypeInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getIsMoon(): bool
    {
        return $this->is_moon;
    }

    public function setIsMoon(bool $isMoon): PlanetTypeInterface
    {
        $this->is_moon = $isMoon;

        return $this;
    }

    public function getResearchId(): ?int
    {
        return $this->research_id;
    }

    public function setResearchId(?int $researchId): PlanetTypeInterface
    {
        $this->research_id = $researchId;

        return $this;
    }

    public function getDatabaseId(): ?int
    {
        return $this->database_id;
    }

    public function setDatabaseId(?int $databaseId): PlanetTypeInterface
    {
        $this->database_id = $databaseId;

        return $this;
    }

    public function getColonizeableFields(): array
    {
        return $this->colonizeable_fields;
    }

    public function setColonizeableFields(array $colonizeableFields): PlanetTypeInterface
    {
        $this->colonizeable_fields = $colonizeableFields;

        return $this;
    }

    public function getBevGrowthRate(): int
    {
        return $this->bev_growth_rate;
    }

    public function setBevGrowthRate(int $bevGroethRate): PlanetTypeInterface
    {
        $this->bev_growth_rate = $bevGroethRate;

        return $this;
    }

    public function getSpecialId(): int
    {
        return $this->special;
    }

    public function setSpecialId(int $specialId): PlanetTypeInterface
    {
        $this->special = $specialId;

        return $this;
    }

    public function getAllowStart(): bool
    {
        return $this->allow_start;
    }

    public function setAllowStart(bool $allowStart): PlanetTypeInterface
    {
        $this->allow_start = $allowStart;

        return $this;
    }

    public function hasRing(): bool
    {
        return $this->getSpecialId() == COLONY_CLASS_SPECIAL_RING;
    }
}
