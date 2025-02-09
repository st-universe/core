<?php

namespace Stu\Orm\Entity;

interface SkillEnhancementLogInterface
{
    public function setUser(UserInterface $user): SkillEnhancementLogInterface;

    public function setEnhancement(SkillEnhancementInterface $enhancement): SkillEnhancementLogInterface;

    public function setCrewName(string $crewName): SkillEnhancementLogInterface;

    public function setShipName(string $shipName): SkillEnhancementLogInterface;

    public function setCrewId(int $crewId): SkillEnhancementLogInterface;

    public function getPromotion(): ?string;

    public function setPromotion(?string $text): SkillEnhancementLogInterface;

    public function setExpertise(int $amount): SkillEnhancementLogInterface;

    public function setExpertiseSum(int $sum): SkillEnhancementLogInterface;

    public function getTimestamp(): int;

    public function setTimestamp(int $date): SkillEnhancementLogInterface;

    public function __toString(): string;
}
