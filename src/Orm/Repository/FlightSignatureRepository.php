<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Orm\Entity\FlightSignature;
use Stu\Orm\Entity\FlightSignatureInterface;
use Stu\Orm\Entity\StarSystemMap;

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
    }

    public function getVisibleSignatureCount($colony): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(distinct fs.ship_id) as count
                    FROM %s fs
                    JOIN %s ssm
                    WITH fs.starsystem_map_id = ssm.id
                    WHERE fs.time > :maxAge
                    AND ssm.sx = :sx
                    AND ssm.sy = :sy
                    AND ssm.systems_id = :systemsId
                    AND fs.user_id != :ignoreId',
                    FlightSignature::class,
                    StarSystemMap::class
                )
            )
            ->setParameters([
                'maxAge' => time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED,
                'sx' => $colony->getSx(),
                'sy' => $colony->getSy(),
                'systemsId' => $colony->getSystem()->getId(),
                'ignoreId' => $colony->getUserId()
            ])
            ->getSingleScalarResult();
    }

    public function getVisibleSignatures($field, bool $isSystem, $ignoreId): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT fs FROM %s fs
                    WHERE fs.time > :maxAge
                    AND fs.%s = :fieldId
                    AND fs.user_id != :ignoreId
                    ORDER BY fs.time desc',
                    FlightSignature::class,
                    $isSystem ? "starsystem_map_id" : "map_id"
                )
            )
            ->setParameters([
                'maxAge' => time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED,
                'fieldId' => $field->getId(),
                'ignoreId' => $ignoreId
            ])
            ->getResult();
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
        $rsm->addScalarResult('shipc', 'shipc', 'integer');

        return $this->getEntityManager()->createNativeQuery(
            'SELECT fs.user_id, count(*) as sc,
                (SELECT race
                FROM stu_user u
                WHERE fs.user_id = u.id),
                count(distinct ship_id) as shipc
            FROM stu_flight_sig fs
            WHERE fs.to_direction != 0
            AND fs.user_id > 100
            AND fs.time > :maxAge
            GROUP BY fs.user_id
            ORDER BY 2 DESC
            LIMIT 10',
            $rsm
        )
            ->setParameter('maxAge', time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED)
            ->getResult();
    }
}
