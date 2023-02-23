<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\ORM\EntityRepository;
use Stu\Orm\Entity\TradeShoutbox;
use Stu\Orm\Entity\TradeShoutboxInterface;

/**
 * @extends EntityRepository<TradeShoutbox>
 */
final class TradeShoutboxRepository extends EntityRepository implements TradeShoutboxRepositoryInterface
{
    public function getByTradeNetwork(int $tradeNetworkId): array
    {
        return $this->findBy(
            ['trade_network_id' => $tradeNetworkId],
            ['id' => 'asc']
        );
    }

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

    public function prototype(): TradeShoutboxInterface
    {
        return new TradeShoutbox();
    }

    public function save(TradeShoutboxInterface $tradeShoutbox): void
    {
        $em = $this->getEntityManager();

        $em->persist($tradeShoutbox);
    }

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
}
