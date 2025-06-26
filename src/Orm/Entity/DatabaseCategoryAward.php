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
use Stu\Orm\Repository\DatabaseCategoryAwardRepository;

#[Table(name: 'stu_database_category_awards')]
#[Entity(repositoryClass: DatabaseCategoryAwardRepository::class)]
class DatabaseCategoryAward
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $category_id;

    #[Column(type: 'integer', nullable: true)]
    private ?int $layer_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $award_id = null;

    #[ManyToOne(targetEntity: DatabaseCategory::class)]
    #[JoinColumn(name: 'category_id', nullable: false, referencedColumnName: 'id')]
    private DatabaseCategory $category;

    #[ManyToOne(targetEntity: Award::class)]
    #[JoinColumn(name: 'award_id', referencedColumnName: 'id')]
    private ?Award $award = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCategoryId(): int
    {
        return $this->category_id;
    }

    public function setLayerId(?int $layerId): DatabaseCategoryAward
    {
        $this->layer_id = $layerId;
        return $this;
    }

    public function getLayerId(): ?int
    {
        return $this->layer_id;
    }

    public function setAwardId(?int $awardId): DatabaseCategoryAward
    {
        $this->award_id = $awardId;
        return $this;
    }

    public function getAwardId(): ?int
    {
        return $this->award_id;
    }

    public function getCategory(): DatabaseCategory
    {
        return $this->category;
    }

    public function getAward(): ?Award
    {
        return $this->award;
    }
}
