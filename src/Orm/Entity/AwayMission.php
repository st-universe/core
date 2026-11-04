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
use Doctrine\ORM\Mapping\UniqueConstraint;

#[Table(name: 'stu_away_mission')]
#[Entity]
#[UniqueConstraint(name: 'away_mission_colony_idx', columns: ['colony_id'])]
class AwayMission
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ManyToOne(targetEntity: Colony::class)]
    #[JoinColumn(name: 'colony_id', referencedColumnName: 'id', nullable: false)]
    private Colony $colony;

    #[ManyToOne(targetEntity: NPCQuest::class)]
    #[JoinColumn(name: 'quest_id', referencedColumnName: 'id', nullable: false)]
    private NPCQuest $quest;

    #[ManyToOne(targetEntity: Commodity::class)]
    #[JoinColumn(name: 'reward_commodity_id', referencedColumnName: 'id', nullable: true)]
    private ?Commodity $rewardCommodity = null;

    #[Column(type: 'string')]
    private string $title = '';

    #[Column(type: 'integer')]
    private int $rewardAmount = 0;

    public function getId(): int
    {
        return $this->id;
    }

    public function getColony(): Colony
    {
        return $this->colony;
    }

    public function setColony(Colony $colony): self
    {
        $this->colony = $colony;

        return $this;
    }

    public function getQuest(): NPCQuest
    {
        return $this->quest;
    }

    public function setQuest(NPCQuest $quest): self
    {
        $this->quest = $quest;

        return $this;
    }

    public function getRewardCommodity(): ?Commodity
    {
        return $this->rewardCommodity;
    }

    public function setRewardCommodity(?Commodity $rewardCommodity): self
    {
        $this->rewardCommodity = $rewardCommodity;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getRewardAmount(): int
    {
        return $this->rewardAmount;
    }

    public function setRewardAmount(int $rewardAmount): self
    {
        $this->rewardAmount = $rewardAmount;

        return $this;
    }
}
