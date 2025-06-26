<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OrderBy;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\DatabaseCategoryRepository;

#[Table(name: 'stu_database_categories')]
#[Entity(repositoryClass: DatabaseCategoryRepository::class)]
class DatabaseCategory
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $description;

    #[Column(type: 'integer')]
    private int $points;

    #[Column(type: 'integer')]
    private int $type;

    #[Column(type: 'integer')]
    private int $sort;

    #[Column(type: 'integer')]
    private int $prestige;

    /**
     * @var ArrayCollection<int, DatabaseEntry>
     */
    #[OneToMany(targetEntity: DatabaseEntry::class, mappedBy: 'category')]
    #[OrderBy(['sort' => 'ASC'])]
    private Collection $entries;

    /**
     * @var ArrayCollection<int, DatabaseCategoryAward>
     */
    #[OneToMany(targetEntity: DatabaseCategoryAward::class, mappedBy: 'category')]
    private Collection $categoryAwards;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
        $this->categoryAwards = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setDescription(string $description): DatabaseCategory
    {
        $this->description = $description;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setPoints(int $points): DatabaseCategory
    {
        $this->points = $points;
        return $this;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setType(int $type): DatabaseCategory
    {
        $this->type = $type;
        return $this;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function setSort(int $sort): DatabaseCategory
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

    /**
     * Returns a list of associated database entries
     *
     * @return array<int, DatabaseEntry>
     */
    public function getEntries(): array
    {
        return $this->entries->toArray();
    }

    /**
     * Returns a list of associated category awards
     *
     * @return array<int, DatabaseCategoryAward>
     */
    public function getCategoryAwards(): array
    {
        return $this->categoryAwards->toArray();
    }
}
