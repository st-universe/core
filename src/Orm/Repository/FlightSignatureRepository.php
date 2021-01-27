<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;

use Stu\Orm\Entity\FlightSignature;
use Stu\Orm\Entity\FlightSignatureInterface;

final class FlightSignatureRepository extends EntityRepository implements FlightSignatureRepositoryInterface
{
    public function prototype(): FlightSignatureInterface
    {
        return new FlightSignature();
    }

    public function saveAll(array $array): void
    {
        $em = $this->getEntityManager();

        foreach ($array as $obj) {
            $em->persist($obj);
        }

        $em->flush();
    }

    public function deleteOldSignatures(int $threshold): void
    {
        $q = $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s fs WHERE fs.time < :maxAge',
                FlightSignature::class
            )
        );
        $q->setParameter('maxAge', time() - $threshold);
        $q->execute();
    }
}
