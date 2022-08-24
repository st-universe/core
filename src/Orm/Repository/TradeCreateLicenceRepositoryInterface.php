<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TradeLicenceCreationInterface;

/**
 * @method null|TradeLicenceCreationInterface find(integer $id)
 */ #
interface TradeCreateLicenceRepositoryInterface extends ObjectRepository
{

    public function prototype(): TradeLicenceCreationInterface;

    public function save(TradeLicenceCreationInterface $post): void;

    public function delete(TradeLicenceCreationInterface $post): void;

    /**
     * @return TradeLicenceCreationInterface[]
     */
    public function getByTradePost(int $posts_id): array;
}