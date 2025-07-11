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
use Stu\Component\Colony\ColonyTypeEnum;
use Stu\Orm\Repository\ResearchRepository;

#[Table(name: 'stu_research')]
#[Entity(repositoryClass: ResearchRepository::class)]
class Research
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'string')]
    private string $name;

    #[Column(type: 'text')]
    private string $description;

    #[Column(type: 'smallint')]
    private int $sort;

    #[Column(type: 'integer')]
    private int $rump_id;

    /**
     * @var int[]
     */
    #[Column(type: 'json')]
    private array $database_entries = [];

    #[Column(type: 'smallint')]
    private int $points;

    #[Column(type: 'integer')]
    private int $commodity_id;

    #[Column(type: 'integer', nullable: true)]
    private ?int $reward_buildplan_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $award_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $needed_award = null;

    #[Column(type: 'smallint', nullable: true, enumType: ColonyTypeEnum::class)]
    private ?ColonyTypeEnum $upper_limit_colony_type = null;

    #[Column(type: 'smallint', nullable: true)]
    private ?int $upper_limit_colony_amount = null;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'commodity_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Commodity $commodity;

    #[ManyToOne(targetEntity: SpacecraftBuildplan::class)]
    #[JoinColumn(name: 'reward_buildplan_id', referencedColumnName: 'id')]
    private ?SpacecraftBuildplan $rewardBuildplan = null;

    #[ManyToOne(targetEntity: Award::class)]
    #[JoinColumn(name: 'award_id', referencedColumnName: 'id')]
    private ?Award $award = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): Research
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): Research
    {
        $this->description = $description;

        return $this;
    }

    public function getSort(): int
    {
        return $this->sort;
    }

    public function setSort(int $sort): Research
    {
        $this->sort = $sort;

        return $this;
    }

    public function getRumpId(): int
    {
        return $this->rump_id;
    }

    /**
     * @return array<int>
     */
    public function getDatabaseEntryIds(): array
    {
        return $this->database_entries;
    }

    /**
     * @param array<int> $databaseEntryIds
     */
    public function setDatabaseEntryIds(array $databaseEntryIds): Research
    {
        $this->database_entries = $databaseEntryIds;

        return $this;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): Research
    {
        $this->points = $points;

        return $this;
    }

    public function getCommodityId(): int
    {
        return $this->commodity_id;
    }

    public function setCommodityId(int $commodityId): Research
    {
        $this->commodity_id = $commodityId;

        return $this;
    }

    public function getUpperPlanetLimit(): int
    {
        return $this->upper_limit_colony_type === ColonyTypeEnum::PLANET
            && $this->upper_limit_colony_amount !== null ? $this->upper_limit_colony_amount : 0;
    }

    public function getUpperMoonLimit(): int
    {
        return $this->upper_limit_colony_type === ColonyTypeEnum::MOON
            && $this->upper_limit_colony_amount !== null ? $this->upper_limit_colony_amount : 0;
    }

    public function getUpperAsteroidLimit(): int
    {
        return $this->upper_limit_colony_type === ColonyTypeEnum::ASTEROID
            && $this->upper_limit_colony_amount !== null ? $this->upper_limit_colony_amount : 0;
    }

    public function getRewardBuildplanId(): ?int
    {
        return $this->reward_buildplan_id;
    }

    public function getCommodity(): Commodity
    {
        return $this->commodity;
    }

    public function getRewardBuildplan(): ?SpacecraftBuildplan
    {
        return $this->rewardBuildplan;
    }

    public function getAward(): ?Award
    {
        return $this->award;
    }

    public function getNeededAwardId(): ?int
    {
        return $this->needed_award;
    }
}
