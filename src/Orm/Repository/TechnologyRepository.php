<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Override;
use Stu\Component\Research\SpecialResearchStatusEnum;
use Stu\Component\Research\TechnologyRecipeKindEnum;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Entity\TechnologyProduction;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Entity\TechnologyRecipeCost;
use Stu\Orm\Entity\TechnologyResearch;
use Stu\Orm\Entity\User;

final class TechnologyRepository implements TechnologyRepositoryInterface
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Override]
    public function getRecipe(int $id): ?TechnologyRecipe
    {
        return $this->entityManager->find(TechnologyRecipe::class, $id);
    }

    #[Override]
    public function getRecipes(): array
    {
        return $this->entityManager->getRepository(TechnologyRecipe::class)->findBy([], ['id' => 'ASC']);
    }

    #[Override]
    public function getCosts(TechnologyRecipe $recipe): array
    {
        return $this->entityManager->getRepository(TechnologyRecipeCost::class)->findBy(['recipe' => $recipe], ['id' => 'ASC']);
    }

    #[Override]
    public function hasNaniteFabrication(User $user): bool
    {
        return $this->entityManager->getRepository(TechnologyResearch::class)->count([
            'user' => $user, 'kind' => TechnologyRecipeKindEnum::NANITE_RESEARCH, 'status' => SpecialResearchStatusEnum::COMPLETED
        ]) > 0;
    }

    #[Override]
    public function hasRunningResearch(User $user): bool
    {
        return $this->entityManager->getRepository(TechnologyResearch::class)->count(['user' => $user, 'status' => SpecialResearchStatusEnum::RUNNING]) > 0;
    }

    #[Override]
    public function getResearchByUser(User $user): array
    {
        return $this->entityManager->getRepository(TechnologyResearch::class)->findBy(['user' => $user], ['id' => 'DESC']);
    }

    #[Override]
    public function getRunningResearch(): array
    {
        return $this->entityManager->createQuery('SELECT r FROM ' . TechnologyResearch::class . ' r WHERE r.status = :status')
            ->setParameter('status', SpecialResearchStatusEnum::RUNNING)->setHint(Query::HINT_REFRESH, true)
            ->setLockMode(LockMode::PESSIMISTIC_WRITE)->getResult();
    }

    #[Override]
    public function getProductionByUser(User $user): array
    {
        return $this->entityManager->getRepository(TechnologyProduction::class)->findBy(['user' => $user, 'status' => SpecialResearchStatusEnum::RUNNING], ['id' => 'ASC']);
    }

    #[Override]
    public function getRunningProduction(): array
    {
        return $this->entityManager->createQuery('SELECT p FROM ' . TechnologyProduction::class . ' p WHERE p.status = :status')
            ->setParameter('status', SpecialResearchStatusEnum::RUNNING)
            ->setHint(Query::HINT_REFRESH, true)->setLockMode(LockMode::PESSIMISTIC_WRITE)->getResult();
    }

    #[Override]
    public function getRunningByField(PlanetField $field): array
    {
        $jobs = [];
        foreach ([TechnologyResearch::class, TechnologyProduction::class] as $class) {
            array_push($jobs, ...$this->entityManager->getRepository($class)->findBy(['fieldId' => $field->getId(), 'status' => SpecialResearchStatusEnum::RUNNING]));
        }
        return $jobs;
    }

    #[Override]
    public function save(TechnologyResearch|TechnologyProduction $job): void
    {
        $this->entityManager->persist($job);
    }

    #[Override]
    public function deleteByUser(User $user): void
    {
        foreach ([TechnologyResearch::class, TechnologyProduction::class] as $class) {
            $this->entityManager->createQuery('DELETE FROM ' . $class . ' j WHERE j.user = :user')->setParameter('user', $user)->execute();
        }
        new UserModuleOptimizationRepository($this->entityManager)->deleteByUser($user);
    }

    #[Override]
    public function detachColony(Colony $colony): void
    {
        foreach ([TechnologyResearch::class, TechnologyProduction::class] as $class) {
            foreach ($this->entityManager->getRepository($class)->findBy(['colony' => $colony]) as $job) {
                $job->setColony(null);
                if ($job->getStatus() === SpecialResearchStatusEnum::RUNNING) {
                    $job->setStatus(SpecialResearchStatusEnum::FAILED);
                }
            }
        }
    }
}
