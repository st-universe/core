<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Stu\Orm\Entity\SkillEnhancementInterface;
use Stu\Orm\Repository\SkillEnhancementRepositoryInterface;

class SkillEnhancementCache implements SkillEnhancementCacheInterface
{
    /** @var array<int, array<int, SkillEnhancementInterface>>|null */
    private ?array $enhancements = null;

    public function __construct(private SkillEnhancementRepositoryInterface $skillEnhancementRepository) {}

    /** @return array<int, SkillEnhancementInterface> */
    public function getSkillEnhancements(SkillEnhancementEnum $enhancementType): ?array
    {
        if ($this->enhancements === null) {
            $this->enhancements = [];

            $enhancements = $this->skillEnhancementRepository->findAll();
            foreach ($enhancements as $enhancement) {
                $type = $enhancement->getType()->value;
                $position = $enhancement->getPosition()->value;
                if (!array_key_exists($type, $this->enhancements)) {
                    $this->enhancements[$type] = [];
                }

                $this->enhancements[$type][$position] = $enhancement;
            }
        }

        if (!array_key_exists($enhancementType->value, $this->enhancements)) {
            return null;
        }

        return $this->enhancements[$enhancementType->value];
    }
}
