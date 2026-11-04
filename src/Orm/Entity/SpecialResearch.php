<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping as ORM;
use Stu\Component\Quest\AwayMission\AwayMissionResearchEnum;
use Stu\Component\Research\SpecialResearchStatusEnum;

#[ORM\Entity]
#[ORM\Table(name: 'stu_special_research')]
#[ORM\UniqueConstraint(name: 'special_research_user_type_idx', columns: ['user_id', 'research_type'])]
#[ORM\Index(name: 'special_research_due_idx', columns: ['status', 'finish_time'])]
class SpecialResearch
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: false)]
    private User $user;

    #[ORM\ManyToOne(targetEntity: Colony::class)]
    #[ORM\JoinColumn(name: 'colony_id', referencedColumnName: 'id', nullable: true)]
    private ?Colony $colony = null;

    #[ORM\Column(name: 'research_type', type: 'string', enumType: AwayMissionResearchEnum::class)]
    private AwayMissionResearchEnum $researchType;

    #[ORM\Column(type: 'string', enumType: SpecialResearchStatusEnum::class)]
    private SpecialResearchStatusEnum $status = SpecialResearchStatusEnum::RUNNING;

    #[ORM\Column(name: 'start_time', type: 'integer')]
    private int $startTime;

    #[ORM\Column(name: 'finish_time', type: 'integer')]
    private int $finishTime;

    #[ORM\Column(name: 'failure_chance', type: 'smallint', options: ['default' => 10])]
    private int $failureChance;

    public function getFailureChance(): int
    {
        return $this->failureChance;
    }

    public function setFailureChance(int $chance): self
    {
        $this->failureChance = $chance;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
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

    public function getColony(): ?Colony
    {
        return $this->colony;
    }

    public function setColony(?Colony $colony): self
    {
        $this->colony = $colony;
        return $this;
    }

    public function getResearchType(): AwayMissionResearchEnum
    {
        return $this->researchType;
    }

    public function setResearchType(AwayMissionResearchEnum $researchType): self
    {
        $this->researchType = $researchType;
        return $this;
    }

    public function getStatus(): SpecialResearchStatusEnum
    {
        return $this->status;
    }

    public function setStatus(SpecialResearchStatusEnum $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getStartTime(): int
    {
        return $this->startTime;
    }

    public function setStartTime(int $startTime): self
    {
        $this->startTime = $startTime;
        return $this;
    }

    public function getFinishTime(): int
    {
        return $this->finishTime;
    }

    public function setFinishTime(int $finishTime): self
    {
        $this->finishTime = $finishTime;
        return $this;
    }
}
