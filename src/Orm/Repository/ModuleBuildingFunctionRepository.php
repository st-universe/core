<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Component\Research\SpecialResearchStatusEnum;
use Stu\Component\Research\TechnologyRecipeKindEnum;
use Stu\Orm\Entity\ModuleBuildingFunction;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\TechnologyRecipe;
use Stu\Orm\Entity\TechnologyResearch;

/**
 * @extends EntityRepository<ModuleBuildingFunction>
 */
final class ModuleBuildingFunctionRepository extends EntityRepository implements ModuleBuildingFunctionRepositoryInterface
{
    #[Override]
    public function save(ModuleBuildingFunction $function): void
    {
        $this->getEntityManager()->persist($function);
    }

    #[Override]
    public function getByBuildingFunctionAndUser(BuildingFunctionEnum $buildingFunction, int $userId): array
    {
        $research = $this->getEntityManager()->createQueryBuilder()->select('r.id')->from(Researched::class, 'r')
            ->where('r.research_id = m.research_id')->andWhere('r.aktiv = :activeState')->andWhere('r.user_id = :userId');
        $naniteRecipe = $this->getEntityManager()->createQueryBuilder()->select('recipe.id')->from(TechnologyRecipe::class, 'recipe')
            ->where('recipe.kind = :productionKind')->andWhere('recipe.outputCommodity = m.commodity');
        $naniteResearch = $this->getEntityManager()->createQueryBuilder()->select('technology.id')->from(TechnologyResearch::class, 'technology')
            ->where('technology.user = :userId')->andWhere('technology.kind = :researchKind')->andWhere('technology.status = :completed');

        return $this->createQueryBuilder('mbf')->innerJoin('mbf.module', 'm')
            ->where('mbf.buildingfunction = :buildingFunction')
            ->andWhere('m.research_id IS NULL OR EXISTS (' . $research->getDQL() . ')')
            ->andWhere('NOT EXISTS (' . $naniteRecipe->getDQL() . ') OR EXISTS (' . $naniteResearch->getDQL() . ')')
            ->setParameter('buildingFunction', $buildingFunction->value)
            ->setParameter('userId', $userId)
            ->setParameter('activeState', 0)
            ->setParameter('productionKind', TechnologyRecipeKindEnum::NANITE_PRODUCTION)
            ->setParameter('researchKind', TechnologyRecipeKindEnum::NANITE_RESEARCH)
            ->setParameter('completed', SpecialResearchStatusEnum::COMPLETED)
            ->orderBy('mbf.module_id')->getQuery()->getResult();
    }
}
