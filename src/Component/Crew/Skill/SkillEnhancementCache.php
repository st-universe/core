<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Stu\Orm\Entity\SkillEnhancement;
use Stu\Orm\Repository\SkillEnhancementRepositoryInterface;

final class SkillEnhancementCache implements SkillEnhancementCacheInterface
{
    /** @var null|array<int, array<int, SkillEnhancement>> */
    private ?array $enhancements = null;

    public function __construct(private SkillEnhancementRepositoryInterface $skillEnhancementRepository) {}

    #[\Override]
    public function getSkillEnhancements(SkillEnhancementEnum $type): ?array
    {
        if ($this->enhancements === null) {
            $this->enhancements = [];

            foreach ($this->skillEnhancementRepository->findAll() as $enhancement) {
                $this->enhancements[$enhancement->getType()->value][$enhancement->getPosition()->value] = $enhancement;
            }
        }

        return $this->enhancements[$type->value] ?? null;
    }
}
