<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\AddShoutBoxEntry;

use AccessViolation;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Trade\View\ShowShoutBoxList\ShowShoutBoxList;
use TradeLicences;
use TradeShoutbox;
use TradeShoutboxData;

final class AddShoutBoxEntry implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_ADD_SHOUTBOX_ENTRY';

    private $addShoutBoxEntryRequest;

    public function __construct(
        AddShoutBoxEntryRequestInterface $addShoutBoxEntryRequest
    ) {
        $this->addShoutBoxEntryRequest = $addShoutBoxEntryRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $msg = $this->addShoutBoxEntryRequest->getMessage();
        $tradeNetworkId = $this->addShoutBoxEntryRequest->getTradeNetworkId();

        if (!TradeLicences::hasLicenceInNetwork($userId, $tradeNetworkId)) {
            throw new AccessViolation();
        }
        $msg = encodeString(substr(strip_tags($msg), 0, 200));
        if (mb_strlen($msg) > 0) {
            $entry = new TradeShoutboxData();
            $entry->setUserId($userId);
            $entry->setDate(time());
            $entry->setTradeNetworkId($tradeNetworkId);
            $entry->setMessage($msg);
            $entry->save();
        }

        TradeShoutbox::deleteHistory($tradeNetworkId);

        $game->setView(ShowShoutBoxList::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
