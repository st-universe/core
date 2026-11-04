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

#[Table(name: 'stu_colony_protection')]
#[Entity]
#[UniqueConstraint(name: 'colony_protection_colony_idx', columns: ['colony_id'])]
class ColonyProtection
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ManyToOne(targetEntity: Colony::class)]
    #[JoinColumn(name: 'colony_id', referencedColumnName: 'id', nullable: false)]
    private Colony $colony;

    #[ManyToOne(targetEntity: NPCQuest::class)]
    #[JoinColumn(name: 'quest_id', referencedColumnName: 'id', nullable: true)]
    private ?NPCQuest $quest = null;

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

    public function getQuest(): ?NPCQuest
    {
        return $this->quest;
    }

    public function setQuest(?NPCQuest $quest): self
    {
        $this->quest = $quest;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->quest === null || $this->quest->getEnd() === null;
    }
}
