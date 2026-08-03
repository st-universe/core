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
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Orm\Repository\SkillEnhancementLogRepository;

#[Table(name: 'stu_skill_enhancement_log')]
#[Entity(repositoryClass: SkillEnhancementLogRepository::class)]
class SkillEnhancementLog
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $crew_id;

    #[Column(type: 'string')]
    private string $crew_name;

    #[Column(type: 'string', nullable: true)]
    private ?string $promotion;

    #[Column(type: 'string')]
    private string $ship_name;

    #[Column(type: 'integer')]
    private int $expertise;

    #[Column(type: 'integer')]
    private int $expertise_sum;

    #[Column(type: 'integer')]
    private int $date;

    #[ManyToOne(targetEntity: User::class)]
    #[JoinColumn(name: 'user_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private User $user;

    #[ManyToOne(targetEntity: SkillEnhancement::class)]
    #[JoinColumn(name: 'enhancement_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SkillEnhancement $enhancement;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCrewId(): int
    {
        return $this->crew_id;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getEnhancement(): SkillEnhancement
    {
        return $this->enhancement;
    }

    public function setUser(User $user): SkillEnhancementLog
    {
        $this->user = $user;

        return $this;
    }

    public function setEnhancement(SkillEnhancement $enhancement): SkillEnhancementLog
    {
        $this->enhancement = $enhancement;

        return $this;
    }

    public function setCrewName(string $crewName): SkillEnhancementLog
    {
        $this->crew_name = $crewName;

        return $this;
    }

    public function setShipName(string $shipName): SkillEnhancementLog
    {
        $this->ship_name = $shipName;

        return $this;
    }

    public function setCrewId(int $crewId): SkillEnhancementLog
    {
        $this->crew_id = $crewId;

        return $this;
    }

    public function getPromotion(): ?string
    {
        return $this->promotion;
    }

    public function setPromotion(?string $promotion): SkillEnhancementLog
    {
        $this->promotion = $promotion;

        return $this;
    }

    public function setExpertise(int $expertise): SkillEnhancementLog
    {
        $this->expertise = $expertise;

        return $this;
    }

    public function setExpertiseSum(int $expertiseSum): SkillEnhancementLog
    {
        $this->expertise_sum = $expertiseSum;

        return $this;
    }

    public function getTimestamp(): int
    {
        return $this->date;
    }

    public function setTimestamp(int $date): SkillEnhancementLog
    {
        $this->date = $date;

        return $this;
    }

    public function __toString(): string
    {
        return sprintf(
            '%s (%s) von der %s steigt auf %d (+%d) Expertise%s fuer %s',
            $this->crew_name,
            $this->enhancement->getPosition()->getDescription(),
            $this->ship_name,
            $this->expertise_sum,
            $this->expertise,
            $this->promotion === null
                ? sprintf(' (Rang %s)', $this->user->getCrewRankName(CrewSkillLevelEnum::getForExpertise($this->expertise_sum)))
                : '',
            $this->enhancement->getDescription()
        );
    }
}
