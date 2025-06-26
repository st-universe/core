<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Override;
use Stu\Orm\Entity\TradeShoutbox;

/**
 * @extends EntityRepository<TradeShoutbox>
 */
final class TradeShoutboxRepository extends EntityRepository implements TradeShoutboxRepositoryInterface
{
    #[Override]
    public function getByTradeNetwork(int $tradeNetworkId): array
    {
        return $this->findBy(
            ['trade_network_id' => $tradeNetworkId],
            ['id' => 'asc']
        );
    }

    #[Override]
    public function deleteHistory(int $tradeNetworkId, int $limit = 30): void
    {
        $entry = $this->findBy(
            ['trade_network_id' => $tradeNetworkId],
            ['id' => 'desc'],
            1,
            $limit
        );
        if ($entry === []) {
            return;
        }
        $entry = current($entry);

        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s tsb WHERE tsb.id <= :entryId',
                    TradeShoutbox::class
                )
            )
            ->setParameters(['entryId' => $entry->getId()])
            ->execute();
    }

    #[Override]
    public function prototype(): TradeShoutbox
    {
        return new TradeShoutbox();
    }

    #[Override]
    public function save(TradeShoutbox $tradeShoutbox): void
    {
        $em = $this->getEntityManager();

        $em->persist($tradeShoutbox);
    }

    #[Override]
    public function truncateByUser(int $userId): void
    {
        $this->getEntityManager()
            ->createQuery(
                sprintf(
                    'DELETE FROM %s tsb WHERE tsb.user_id = :userId',
                    TradeShoutbox::class
                )
            )
            ->setParameters(['userId' => $userId])
            ->execute();
    }

    #[Override]
    public function truncateAllEntries(): void
    {
        $this->getEntityManager()->createQuery(
            sprintf(
                'DELETE FROM %s ts',
                TradeShoutbox::class
            )
        )->execute();
    }
}
