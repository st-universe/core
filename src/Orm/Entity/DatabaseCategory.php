<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\DatabaseCategoryRepository")
 * @Table(name="stu_database_categories")
 */
class DatabaseCategory implements DatabaseCategoryInterface
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
     * @Column(type="string")
     */
    private ?string $description = null;

    /**
     * @Column(type="integer")
     */
    private ?int $points = null;

    /**
     * @Column(type="integer")
     */
    private ?int $type = null;

    /**
     * @Column(type="integer")
     */
    private ?int $sort = null;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $prestige;

    /**
     * @Column(type="integer", nullable=true) *
     *
     * @var null|int
     */
    private $award_id;

    /**
     * @var Collection<int, DatabaseEntryInterface>
     *
     * @OneToMany(targetEntity="Stu\Orm\Entity\DatabaseEntry", mappedBy="category")
     * @OrderBy({"sort" = "ASC"})
     */
    private Collection $entries;

    /**
     * @var AwardInterface
     *
     * @ManyToOne(targetEntity="Award")
     * @JoinColumn(name="award_id", referencedColumnName="id")
     */
    private $award;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setDescription(string $description): DatabaseCategoryInterface
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setPoints(int $points): DatabaseCategoryInterface
    {
        $this->points = $points;

        return $this;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setType(int $type): DatabaseCategoryInterface
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setSort(int $sort): DatabaseCategoryInterface
    {
        $this->sort = $sort;

        return $this;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function getPrestige(): int
    {
        return $this->prestige;
    }

    public function getAward(): ?AwardInterface
    {
        return $this->award;
    }

    public function getEntries(): array
    {
        return $this->entries->toArray();
    }
}
