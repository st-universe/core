<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\ShipLog;
use Stu\Orm\Entity\SpacecraftLogScan;
use Stu\Orm\Entity\User;

/**
 * @extends EntityRepository<ShipLog>
 */
final class ShipLogRepository extends EntityRepository implements ShipLogRepositoryInterface
{
    private const int MAX_DATABASE_TIMESTAMP = 2147483647;

    #[\Override]
    public function prototype(): ShipLog
    {
        return new ShipLog();
    }

    #[\Override]
    public function save(ShipLog $shipLog): void
    {
        $em = $this->getEntityManager();

        $em->persist($shipLog);
    }

    #[\Override]
    public function delete(ShipLog $shipLog): void
    {
        $em = $this->getEntityManager();

        $em->remove($shipLog);
    }

    #[\Override]
    public function getBySpacecraftId(int $spacecraftId, bool $includePrivate = true): array
    {
        $query = $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT sl
                    FROM %s sl
                    WHERE sl.spacecraft_id = :spacecraftId
                    AND sl.deleted IS NULL
                    %s
                    ORDER BY sl.date DESC, sl.id DESC',
                    ShipLog::class,
                    $includePrivate ? '' : 'AND sl.is_private = false'
                )
            )
            ->setParameter('spacecraftId', $spacecraftId);

        return $query->getResult();
    }

    #[\Override]
    public function getBySpacecraftIdUntil(int $spacecraftId, int $date, bool $includePrivate = true): array
    {
        $date = min($date, self::MAX_DATABASE_TIMESTAMP);

        $query = $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT sl
                    FROM %s sl
                    WHERE sl.spacecraft_id = :spacecraftId
                    AND sl.date <= :date
                    AND sl.deleted IS NULL
                    %s
                    ORDER BY sl.date DESC, sl.id DESC',
                    ShipLog::class,
                    $includePrivate ? '' : 'AND sl.is_private = false'
                )
            )
            ->setParameters([
                'spacecraftId' => $spacecraftId,
                'date' => $date
            ]);

        return $query->getResult();
    }

    #[\Override]
    public function hasVisibleLogbook(int $spacecraftId): bool
    {
        return $this->count([
            'spacecraft_id' => $spacecraftId,
            'is_private' => false,
            'deleted' => null
        ]) > 0;
    }

    #[\Override]
    public function getGroupedLogbooksForProfile(User $profileUser, User $visitor): array
    {
        $isOwnProfile = $profileUser->getId() === $visitor->getId();
        $rows = $isOwnProfile
            ? $this->getOwnProfileRows($profileUser)
            : $this->getScannedProfileRows($profileUser, $visitor);

        return $this->groupLogbookRows($rows);
    }

    /** @return array<int, array{0: ShipLog, scanDate: ?int}> */
    private function getOwnProfileRows(User $profileUser): array
    {
        $logs = $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT sl
                    FROM %s sl
                    WHERE sl.user = :profileUser
                    AND sl.deleted IS NULL
                    ORDER BY sl.spacecraft_id ASC, sl.date DESC, sl.id DESC',
                    ShipLog::class
                )
            )
            ->setParameter('profileUser', $profileUser)
            ->getResult();

        return array_map(
            fn (ShipLog $log): array => [0 => $log, 'scanDate' => null],
            $logs
        );
    }

    /** @return array<int, array{0: ShipLog, scanDate: int}> */
    private function getScannedProfileRows(User $profileUser, User $visitor): array
    {
        return $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'SELECT sl, scan.date as scanDate
                    FROM %s sl
                    JOIN %s scan WITH scan.spacecraft_id = sl.spacecraft_id
                    WHERE sl.user = :profileUser
                    AND scan.user = :visitor
                    AND sl.date <= scan.date
                    AND sl.is_private = false
                    AND sl.deleted IS NULL
                    ORDER BY sl.spacecraft_id ASC, sl.date DESC, sl.id DESC',
                    ShipLog::class,
                    SpacecraftLogScan::class
                )
            )
            ->setParameters([
                'profileUser' => $profileUser,
                'visitor' => $visitor
            ])
            ->getResult();
    }

    /**
     * @param array<int, array{0: ShipLog, scanDate: ?int}> $rows
     *
     * @return array<int, array{spacecraftId: int, name: string, rumpId: ?int, scanDate: ?int, logs: array<int, ShipLog>}>
     */
    private function groupLogbookRows(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $log = $row[0];
            $spacecraftId = $log->getSpacecraftId();

            if (!isset($grouped[$spacecraftId])) {
                $grouped[$spacecraftId] = [
                    'spacecraftId' => $spacecraftId,
                    'name' => $log->getName() ?? sprintf(_('Unbekanntes Schiff %d'), $spacecraftId),
                    'rumpId' => $log->getRumpId(),
                    'scanDate' => $row['scanDate'] !== null ? (int)$row['scanDate'] : null,
                    'logs' => []
                ];
            }

            $grouped[$spacecraftId]['logs'][] = $log;
        }

        return array_values($grouped);
    }
}
