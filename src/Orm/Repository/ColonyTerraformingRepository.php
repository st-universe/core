<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\ColonyTerraforming;

/**
 * @extends EntityRepository<ColonyTerraforming>
 */
final class ColonyTerraformingRepository extends EntityRepository implements ColonyTerraformingRepositoryInterface
{
    #[Override]
    public function prototype(): ColonyTerraforming
    {
        return new ColonyTerraforming();
    }

    #[Override]
    public function save(ColonyTerraforming $terraforming): void
    {
        $em = $this->getEntityManager();

        $em->persist($terraforming);
    }

    #[Override]
    public function delete(ColonyTerraforming $terraforming): void
    {
        $em = $this->getEntityManager();

        $em->remove($terraforming);
    }

    #[Override]
    public function getByColony(array $colonies): array
    {
        return $this->findBy([
            'colonies_id' => $colonies
        ]);
    }

    #[Override]
    public function getByColonyAndField(int $colonyId, int $fieldId): ?ColonyTerraforming
    {
        return $this->findOneBy([
            'colonies_id' => $colonyId,
            'field_id' => $fieldId
        ]);
    }

    #[Override]
    public function getFinishedJobs(): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT t from %s t WHERE t.finished < :finishDate',
                    ColonyTerraforming::class,
                )
            )
            ->setParameters([
                'finishDate' => time(),
            ])
            ->getResult();
    }
}
