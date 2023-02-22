<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ColonyTerraforming;
use Stu\Orm\Entity\ColonyTerraformingInterface;

/**
 * @extends EntityRepository<ColonyTerraforming>
 */
final class ColonyTerraformingRepository extends EntityRepository implements ColonyTerraformingRepositoryInterface
{
    public function prototype(): ColonyTerraformingInterface
    {
        return new ColonyTerraforming();
    }

    public function save(ColonyTerraformingInterface $terraforming): void
    {
        $em = $this->getEntityManager();

        $em->persist($terraforming);
    }

    public function delete(ColonyTerraformingInterface $terraforming): void
    {
        $em = $this->getEntityManager();

        $em->remove($terraforming);
    }

    public function getByColony(array $colonies): array
    {
        return $this->findBy([
            'colonies_id' => $colonies,
        ]);
    }

    public function getByColonyAndField(int $colonyId, int $fieldId): ?ColonyTerraformingInterface
    {
        return $this->findOneBy([
            'colonies_id' => $colonyId,
            'field_id' => $fieldId,
        ]);
    }

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
