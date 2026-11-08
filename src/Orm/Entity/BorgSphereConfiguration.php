<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use LogicException;

#[Entity]
#[Table(name: 'stu_borg_sphere_configuration')]
class BorgSphereConfiguration
{
    #[Id]
    #[Column(type: 'integer')]
    private int $id;

    #[ManyToOne(targetEntity: Colony::class)]
    #[JoinColumn(name: 'colony_id', referencedColumnName: 'id', nullable: false)]
    private Colony $colony;

    #[ManyToOne(targetEntity: NPCQuest::class)]
    #[JoinColumn(name: 'quest_id', referencedColumnName: 'id', nullable: false)]
    private NPCQuest $quest;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ManyToOne(targetEntity: SpacecraftBuildplan::class)]
    #[JoinColumn(name: 'buildplan_id', referencedColumnName: 'id', nullable: false)]
    private SpacecraftBuildplan $buildplan;

    #[Column(name: 'end_time', type: 'integer')]
    private int $endTime;

    /** @var array<string, int> */
    #[Column(type: 'json')]
    private array $settings = [];

    public function getId(): int
    {
        return $this->id;
    }
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
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
    public function getUser(): User
    {
        return $this->user;
    }
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }
    public function getBuildplan(): SpacecraftBuildplan
    {
        return $this->buildplan;
    }
    public function setBuildplan(SpacecraftBuildplan $buildplan): self
    {
        $this->buildplan = $buildplan;
        return $this;
    }
    public function getEndTime(): int
    {
        return $this->endTime;
    }
    public function setEndTime(int $time): self
    {
        $this->endTime = $time;
        return $this;
    }
    public function getSetting(string $key): int
    {
        return $this->settings[$key] ?? throw new LogicException('Missing Borg sphere setting: ' . $key);
    }
    /** @return array<string, int> */
    public function getSettings(): array
    {
        return $this->settings;
    }
    /** @param array<string, int> $settings */
    public function setSettings(array $settings): self
    {
        $this->settings = $settings;
        return $this;
    }
    public function isActive(int $time): bool
    {
        return $this->quest->getStart() <= $time && $this->quest->getEnd() === null && $time < $this->endTime;
    }
}
