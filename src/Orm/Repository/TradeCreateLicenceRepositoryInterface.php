<?php

declare(strict_types=1);

namespace Stu\Orm\Repository;

use Doctrine\Persistence\ObjectRepository;
use Stu\Orm\Entity\TradeCreateLicenceInterface;

/**
 * @method null|TradeCreateLicenceInterface find(integer $id)
 */
interface TradeCreateLicenceRepositoryInterface extends ObjectRepository
{
    public function prototype(): TradeCreateLicenceInterface;

    public function save(TradeCreateLicenceInterface $post): void;

    public function delete(TradeCreateLicenceInterface $post): void;

}