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
use Override;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Orm\Repository\SkillEnhancementLogRepository;

#[Table(name: 'stu_skill_enhancement_log')]
#[Entity(repositoryClass: SkillEnhancementLogRepository::class)]
class SkillEnhancementLog implements SkillEnhancementLogInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id;

    #[Column(type: 'integer')]
    private int $enhancement_id;

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

    #[ManyToOne(targetEntity: 'User')]
    #[JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private UserInterface $user;

    #[ManyToOne(targetEntity: 'SkillEnhancement')]
    #[JoinColumn(name: 'enhancement_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SkillEnhancementInterface $enhancement;

    #[Override]
    public function setUser(UserInterface $user): SkillEnhancementLogInterface
    {
        $this->user = $user;

        return $this;
    }

    #[Override]
    public function setEnhancement(SkillEnhancementInterface $enhancement): SkillEnhancementLogInterface
    {
        $this->enhancement = $enhancement;

        return $this;
    }

    #[Override]
    public function setCrewName(string $crewName): SkillEnhancementLogInterface
    {
        $this->crew_name = $crewName;

        return $this;
    }

    #[Override]
    public function setShipName(string $shipName): SkillEnhancementLogInterface
    {
        $this->ship_name = $shipName;

        return $this;
    }

    #[Override]
    public function setCrewId(int $crewId): SkillEnhancementLogInterface
    {
        $this->crew_id = $crewId;

        return $this;
    }

    #[Override]
    public function getPromotion(): ?string
    {
        return $this->promotion;
    }

    #[Override]
    public function setPromotion(?string $text): SkillEnhancementLogInterface
    {
        $this->promotion = $text;

        return $this;
    }

    #[Override]
    public function setExpertise(int $amount): SkillEnhancementLogInterface
    {
        $this->expertise = $amount;

        return $this;
    }

    #[Override]
    public function setExpertiseSum(int $sum): SkillEnhancementLogInterface
    {
        $this->expertise_sum = $sum;

        return $this;
    }

    #[Override]
    public function getTimestamp(): int
    {
        return $this->date;
    }

    #[Override]
    public function setTimestamp(int $date): SkillEnhancementLogInterface
    {
        $this->date = $date;

        return $this;
    }

    #[Override]
    public function __toString(): string
    {
        return sprintf(
            '%s (%s) von der %s steigt auf %d (+%d) Expertise%s für %s',
            $this->crew_name,
            $this->enhancement->getPosition()->getDescription(),
            $this->ship_name,
            $this->expertise_sum,
            $this->expertise,
            $this->promotion === null ? sprintf(' (Rang %s)', CrewSkillLevelEnum::getForExpertise($this->expertise)->getDescription()) : '',
            $this->enhancement->getDescription()
        );
    }
}
