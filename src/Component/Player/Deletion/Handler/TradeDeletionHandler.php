<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;
use Stu\Orm\Repository\TradeShoutboxRepositoryInterface;
use Stu\Orm\Repository\TradeStorageRepositoryInterface;

final class TradeDeletionHandler implements PlayerDeletionHandlerInteface
{

    private $tradeLicenseRepository;

    private $tradeOfferRepository;

    private $tradeStorageRepository;

    private $tradeShoutboxRepository;

    public function __construct(
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeOfferRepositoryInterface $tradeOfferRepository,
        TradeStorageRepositoryInterface $tradeStorageRepository,
        TradeShoutboxRepositoryInterface $tradeShoutboxRepository
    ) {
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeOfferRepository = $tradeOfferRepository;
        $this->tradeStorageRepository = $tradeStorageRepository;
        $this->tradeShoutboxRepository = $tradeShoutboxRepository;
    }

    public function delete(UserInterface $user): void
    {
        $userId = $user->getId();

        $this->tradeLicenseRepository->truncateByUser($userId);
        $this->tradeOfferRepository->truncateByUser($userId);
        $this->tradeStorageRepository->truncateByUser($userId);
        $this->tradeShoutboxRepository->truncateByUser($userId);
    }
}
