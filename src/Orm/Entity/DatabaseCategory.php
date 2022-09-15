<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\DatabaseCategoryRepository")
 * @Table(name="stu_database_categories")
 **/
class DatabaseCategory implements DatabaseCategoryInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="string") * */
    private $description;

    /** @Column(type="integer") * */
    private $points;

    /** @Column(type="integer") * */
    private $type;

    /** @Column(type="integer") * */
    private $sort;

    /** @Column(type="integer") * */
    private $prestige;

    /** @Column(type="integer", nullable=true) * */
    private $award_id;

    /**
     * @OneToMany(targetEntity="Stu\Orm\Entity\DatabaseEntry", mappedBy="category")
     * @OrderBy({"sort" = "ASC"})
     */
    private $entries;

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

    public function getEntries(): array
    {
        return $this->entries->toArray();
    }
}
