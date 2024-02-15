<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;
use Stu\Component\Ship\FlightSignatureVisibilityEnum;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\FlightSignature;
use Stu\Orm\Entity\FlightSignatureInterface;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Entity\UserInterface;

/**
 * @extends EntityRepository<FlightSignature>
 */
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

    public function save(FlightSignatureInterface $item): void
    {
        $em = $this->getEntityManager();
        $em->persist($item);
    }

    public function getVisibleSignatureCount(ColonyInterface $colony): int
    {
        return (int) $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(DISTINCT CONCAT(fs.ship_id, fs.ship_name)) as count
                    FROM %s fs
                    JOIN %s ssm
                    WITH fs.starsystem_map_id = ssm.id
                    WHERE (fs.is_cloaked = false AND fs.time > :maxAgeUncloaked
                      OR fs.is_cloaked = true AND fs.time > :maxAgeCloaked)
                    AND ssm.sx = :sx
                    AND ssm.sy = :sy
                    AND ssm.systems_id = :systemsId
                    AND fs.user_id != :ignoreId',
                    FlightSignature::class,
                    StarSystemMap::class
                )
            )
            ->setParameters([
                'maxAgeUncloaked' => time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED,
                'maxAgeCloaked' => time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_CLOAKED,
                'sx' => $colony->getSx(),
                'sy' => $colony->getSy(),
                'systemsId' => $colony->getSystem()->getId(),
                'ignoreId' => $colony->getUserId()
            ])
            ->getSingleScalarResult();
    }




    public function getVisibleSignatures(int $fieldId, bool $isSystem, int $ignoreId): array
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
                'fieldId' => $fieldId,
                'ignoreId' => $ignoreId
            ])
            ->getResult();
    }

    public function createSignatureRangeResultMapping(): ResultSetMapping
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('minx', 'minx', 'integer');
        $rsm->addScalarResult('maxx', 'maxx', 'integer');
        $rsm->addScalarResult('miny', 'miny', 'integer');
        $rsm->addScalarResult('maxy', 'maxy', 'integer');

        return $rsm;
    }

    public function getSignatureRange(): array
    {
        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT COALESCE(min(m.cx),0) as minx, COALESCE(max(m.cx),0) as maxx,
                    COALESCE(min(m.cy),0) as miny, COALESCE(max(m.cy),0) as maxy
                FROM stu_flight_sig fs
                JOIN stu_map m ON m.id = fs.map_id',
                $this->createSignatureRangeResultMapping()
            )
            ->getResult();
    }

    public function getSignatureRangeForShip(int $shipId): array
    {
        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT COALESCE(min(m.cx),0) as minx, COALESCE(max(m.cx),0) as maxx, COALESCE(min(m.cy),0) as miny, COALESCE(max(m.cy),0) as maxy
                FROM stu_flight_sig fs
                JOIN stu_map m ON m.id = fs.map_id
                WHERE fs.ship_id = :shipId',
                $this->createSignatureRangeResultMapping()
            )
            ->setParameter('shipId', $shipId)
            ->getResult();
    }

    public function getSignatureRangeForUser(int $userId): array
    {
        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT COALESCE(min(m.cx),0) as minx, COALESCE(max(m.cx),0) as maxx, COALESCE(min(m.cy),0) as miny, COALESCE(max(m.cy),0) as maxy
                FROM stu_flight_sig fs
                JOIN stu_map m ON m.id = fs.map_id
                WHERE fs.user_id = :userId',
                $this->createSignatureRangeResultMapping()
            )
            ->setParameter('userId', $userId)
            ->getResult();
    }

    public function getSignatureRangeForAlly(int $allyId): array
    {
        return $this->getEntityManager()
            ->createNativeQuery(
                'SELECT COALESCE(min(m.cx),0) as minx, COALESCE(max(m.cx),0) as maxx, COALESCE(min(m.cy),0) as miny, COALESCE(max(m.cy),0) as maxy
                FROM stu_flight_sig fs
                JOIN stu_map m ON m.id = fs.map_id
                JOIN stu_user u	ON fs.user_id = u.id
                WHERE u.allys_id = :allyId',
                $this->createSignatureRangeResultMapping()
            )
            ->setParameter('allyId', $allyId)
            ->getResult();
    }

    public function deleteOldSignatures(int $threshold): void
    {
        $q = $this->getEntityManager()
            ->createQuery(
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

        return $this
            ->getEntityManager()
            ->createNativeQuery(
                'SELECT fs.user_id, count(*) as sc,
                (SELECT race
                FROM stu_user u
                WHERE fs.user_id = u.id),
                count(distinct ship_id) as shipc
                FROM stu_flight_sig fs
                WHERE fs.to_direction != 0
                AND fs.user_id > :firstUserId
                AND fs.time > :maxAge
                GROUP BY fs.user_id
                ORDER BY 2 DESC
                LIMIT 10',
                $rsm
            )
            ->setParameters([
                'maxAge' => time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED,
                'firstUserId' => UserEnum::USER_FIRST_ID
            ])
            ->getResult();
    }

    public function getSignaturesForUser(UserInterface $user): int
    {
        return (int)$this
            ->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT count(fs.id)
                    FROM %s fs
                    WHERE fs.to_direction != 0
                    AND fs.user_id  = :userId
                    AND fs.time > :maxAge',
                    FlightSignature::class
                )
            )
            ->setParameters([
                'maxAge' => time() - FlightSignatureVisibilityEnum::SIG_VISIBILITY_UNCLOAKED,
                'userId' => $user->getId()
            ])
            ->getSingleScalarResult();
    }

    public function truncateAllSignatures(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s fs',
                FlightSignature::class
            )
        )->execute();
    }
}
