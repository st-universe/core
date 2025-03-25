<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\AddShoutBoxEntry;

use Override;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\View\ShowShoutBoxList\ShowShoutBoxList;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeShoutboxRepositoryInterface;

final class AddShoutBoxEntry implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ADD_SHOUTBOX_ENTRY';

    public function __construct(private AddShoutBoxEntryRequestInterface $addShoutBoxEntryRequest, private TradeShoutboxRepositoryInterface $tradeShoutboxRepository, private TradeLicenseRepositoryInterface $tradeLicenseRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $msg = $this->addShoutBoxEntryRequest->getMessage();
        $tradeNetworkId = $this->addShoutBoxEntryRequest->getTradeNetworkId();

        if (!$this->tradeLicenseRepository->hasLicenseByUserAndNetwork($userId, $tradeNetworkId)) {
            throw new AccessViolationException();
        }
        $msg = substr(strip_tags($msg), 0, 200);
        if (mb_strlen($msg) > 0) {
            $entry = $this->tradeShoutboxRepository
                ->prototype()
                ->setUser($game->getUser())
                ->setDate(time())
                ->setTradeNetworkId($tradeNetworkId)
                ->setMessage($msg);

            $this->tradeShoutboxRepository->save($entry);
        }

        $this->tradeShoutboxRepository->deleteHistory($tradeNetworkId);

        $game->setView(ShowShoutBoxList::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
