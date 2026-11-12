<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Orm\Entity\SkillEnhancement;
use Stu\Orm\Repository\SkillEnhancementRepositoryInterface;

final class SkillEnhancementCache implements SkillEnhancementCacheInterface
{
    /** @var null|array<int, array<int, SkillEnhancement>> */
    private ?array $enhancements = null;

    public function __construct(
        private SkillEnhancementRepositoryInterface $skillEnhancementRepository,
        private EntityManagerInterface $entityManager
    ) {}

    #[\Override]
    public function getSkillEnhancements(SkillEnhancementEnum $type): ?array
    {
        if (
            $this->enhancements === null
            || array_any(
                $this->enhancements[$type->value] ?? [],
                fn (SkillEnhancement $enhancement): bool => !$this->entityManager->contains($enhancement)
            )
        ) {
            $this->enhancements = [];

            foreach ($this->skillEnhancementRepository->findAll() as $enhancement) {
                $this->enhancements[$enhancement->getType()->value][$enhancement->getPosition()->value] = $enhancement;
            }
        }

        return $this->enhancements[$type->value] ?? null;
    }
}
