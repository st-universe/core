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
use Override;
use Stu\Orm\Repository\DatabaseCategoryRepository;

#[Table(name: 'stu_database_categories')]
#[Entity(repositoryClass: DatabaseCategoryRepository::class)]
class DatabaseCategory implements DatabaseCategoryInterface
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
     * @var ArrayCollection<int, DatabaseEntryInterface>
     */
    #[OneToMany(targetEntity: DatabaseEntry::class, mappedBy: 'category')]
    #[OrderBy(['sort' => 'ASC'])]
    private Collection $entries;

    /**
     * @var ArrayCollection<int, DatabaseCategoryAwardInterface>
     */
    #[OneToMany(targetEntity: DatabaseCategoryAward::class, mappedBy: 'category')]
    private Collection $categoryAwards;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
        $this->categoryAwards = new ArrayCollection();
    }

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function setDescription(string $description): DatabaseCategoryInterface
    {
        $this->description = $description;
        return $this;
    }

    #[Override]
    public function getDescription(): string
    {
        return $this->description;
    }

    #[Override]
    public function setPoints(int $points): DatabaseCategoryInterface
    {
        $this->points = $points;
        return $this;
    }

    #[Override]
    public function getPoints(): int
    {
        return $this->points;
    }

    #[Override]
    public function setType(int $type): DatabaseCategoryInterface
    {
        $this->type = $type;
        return $this;
    }

    #[Override]
    public function getType(): int
    {
        return $this->type;
    }

    #[Override]
    public function setSort(int $sort): DatabaseCategoryInterface
    {
        $this->sort = $sort;
        return $this;
    }

    #[Override]
    public function getSort(): int
    {
        return $this->sort;
    }

    #[Override]
    public function getPrestige(): int
    {
        return $this->prestige;
    }

    #[Override]
    public function getEntries(): array
    {
        return $this->entries->toArray();
    }

    #[Override]
    public function getCategoryAwards(): array
    {
        return $this->categoryAwards->toArray();
    }
}
