<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

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

    public function getFlightsTop10(): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('user_id', 'user_id', 'integer');
        $rsm->addScalarResult('sc', 'sc', 'integer');
        $rsm->addScalarResult('race', 'race', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT user_id, count(*) as sc, (SELECT race
                                                FROM stu_user u
                                                WHERE fs.user_id = u.id)
            FROM stu_flight_sig fs
            WHERE to_direction != 0
            GROUP BY user_id
            ORDER BY 2 DESC
            LIMIT 10',
            $rsm
        )->getResult();
    }
}
