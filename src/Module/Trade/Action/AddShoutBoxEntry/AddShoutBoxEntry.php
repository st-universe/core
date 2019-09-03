<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\AddShoutBoxEntry;

use AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\View\ShowShoutBoxList\ShowShoutBoxList;
use Stu\Orm\Repository\TradeShoutboxRepositoryInterface;
use TradeLicences;

final class AddShoutBoxEntry implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_ADD_SHOUTBOX_ENTRY';

    private $addShoutBoxEntryRequest;

    private $tradeShoutboxRepository;

    public function __construct(
        AddShoutBoxEntryRequestInterface $addShoutBoxEntryRequest,
        TradeShoutboxRepositoryInterface $tradeShoutboxRepository
    ) {
        $this->addShoutBoxEntryRequest = $addShoutBoxEntryRequest;
        $this->tradeShoutboxRepository = $tradeShoutboxRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $msg = $this->addShoutBoxEntryRequest->getMessage();
        $tradeNetworkId = $this->addShoutBoxEntryRequest->getTradeNetworkId();

        if (!TradeLicences::hasLicenceInNetwork($userId, $tradeNetworkId)) {
            throw new AccessViolation();
        }
        $msg = substr(strip_tags($msg), 0, 200);
        if (mb_strlen($msg) > 0) {
            $entry = $this->tradeShoutboxRepository
                ->prototype()
                ->setUserId($userId)
                ->setDate(time())
                ->setTradeNetworkId($tradeNetworkId)
                ->setMessage($msg);

            $this->tradeShoutboxRepository->save($entry);
        }

        $this->tradeShoutboxRepository->deleteHistory($tradeNetworkId);

        $game->setView(ShowShoutBoxList::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
