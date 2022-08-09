<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TradeLicenceCreationInterface;


interface TradeCreateLicenceRepositoryInterface extends ObjectRepository
{
    /**
    * @return TradeLicenceCreationInterface[]
    */
    public function prototype(): TradeLicenceCreationInterface;

    public function save(TradeLicenceCreationInterface $post): void;

    public function delete(TradeLicenceCreationInterface $post): void;
}