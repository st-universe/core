<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Component\Colony\ColonyEnum;
use Stu\Component\Colony\ColonyTypeEnum;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ColonyClassRepository")
 * @Table(
 *     name="stu_colonies_classes",
 *     indexes={
 *     }
 * )
 **/
class ColonyClass implements ColonyClassInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="string") */
    private $name = '';

    /** @Column(type="boolean", nullable=true) */
    private $is_moon;

    /** @Column(type="integer", nullable=true) * */
    private $type;

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
     * @OneToMany(targetEntity="ColonyClassDeposit", mappedBy="colonyClass")
     */
    private $colonyClassDeposits;

    public function __construct()
    {
        $this->colonyClassDeposits = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): ColonyClassInterface
    {
        $this->name = $name;

        return $this;
    }

    public function getIsMoon(): bool
    {
        return $this->type === ColonyTypeEnum::COLONY_TYPE_MOON;
    }

    public function getDatabaseId(): ?int
    {
        return $this->database_id;
    }

    public function setDatabaseId(?int $databaseId): ColonyClassInterface
    {
        $this->database_id = $databaseId;

        return $this;
    }

    public function getColonizeableFields(): array
    {
        return $this->colonizeable_fields;
    }

    public function setColonizeableFields(array $colonizeableFields): ColonyClassInterface
    {
        $this->colonizeable_fields = $colonizeableFields;

        return $this;
    }

    public function getBevGrowthRate(): int
    {
        return $this->bev_growth_rate;
    }

    public function setBevGrowthRate(int $bevGroethRate): ColonyClassInterface
    {
        $this->bev_growth_rate = $bevGroethRate;

        return $this;
    }

    public function getSpecialId(): int
    {
        return $this->special;
    }

    public function setSpecialId(int $specialId): ColonyClassInterface
    {
        $this->special = $specialId;

        return $this;
    }

    public function getAllowStart(): bool
    {
        return $this->allow_start;
    }

    public function setAllowStart(bool $allowStart): ColonyClassInterface
    {
        $this->allow_start = $allowStart;

        return $this;
    }

    public function getColonyClassDeposits(): Collection
    {
        return $this->colonyClassDeposits;
    }

    public function hasRing(): bool
    {
        return $this->getSpecialId() == ColonyEnum::COLONY_CLASS_SPECIAL_RING;
    }
}
