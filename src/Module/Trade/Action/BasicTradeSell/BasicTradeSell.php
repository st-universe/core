<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\BasicTradeSell;

use Override;
use request;
use Stu\Component\Trade\TradeEnum;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\Lib\BasicTradeItem;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowBasicTrade\ShowBasicTrade;
use Stu\Orm\Entity\Storage;
use Stu\Orm\Repository\BasicTradeRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class BasicTradeSell implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_BASIC_SELL';

    public function __construct(private TradeLibFactoryInterface $tradeLibFactory, private BasicTradeRepositoryInterface $basicTradeRepository, private TradePostRepositoryInterface $tradePostRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowBasicTrade::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $tradePostId = request::postIntFatal('tpid');
        $uniqId = request::postStringFatal('uid');
        $basicTrade = $this->basicTradeRepository->getByUniqId($uniqId);

        if ($basicTrade === null) {
            return;
        }

        $isNewest = $this->basicTradeRepository->isNewest($basicTrade);

        if ($userId < 100) {
            $game->getInfo()->addInformation(_('NPCs können dieses Angebot nicht annehmen'));
            return;
        }

        if (!$isNewest) {
            $game->getInfo()->addInformation("Kurs wurde zwischenzeitlich aktualisiert - es konnte nicht verkauft werden");
            return;
        }

        $tradePost = $this->tradePostRepository->find($tradePostId);

        if ($tradePost === null) {
            return;
        }

        $storageManager = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $game->getUser());

        if ($storageManager->getFreeStorage() <= 0) {
            $game->getInfo()->addInformation("Dein Warenkonto auf diesem Handelsposten ist überfüllt - es konnte nicht gekauft werden");
            return;
        }

        /** @var ?Storage */
        $commodityStorage = $storageManager->getStorage()->get($basicTrade->getCommodity()->getId());
        $sellValue = (int)($basicTrade->getValue() / BasicTradeItem::BASIC_TRADE_VALUE_SCALE * BasicTradeItem::BASIC_TRADE_SELL_BUY_ALPHA);

        if ($commodityStorage === null || $commodityStorage->getAmount() < $sellValue) {
            $game->getInfo()->addInformation("Dein Warenkonto verfügt nicht über ausreichend Waren - es konnte nicht verkauft werden");
            return;
        }

        $latestRates = $this->basicTradeRepository->getLatestRates($basicTrade);

        $newValue = null;
        $summand = TradeEnum::BASIC_TRADE_SELL_MODIFIER;

        foreach ($latestRates as $rate) {
            if ($newValue === null) {
                $newValue = $rate->getValue();
            } else {
                $summand += $rate->getBuySell();
            }
        }
        $newValue += (int)($summand * BasicTradeItem::BASIC_TRADE_VALUE_SCALE / (count($latestRates) + 1));

        $newBasicTrade = $this->basicTradeRepository->prototype();
        $newBasicTrade->setFaction($basicTrade->getFaction());
        $newBasicTrade->setCommodity($basicTrade->getCommodity());
        $newBasicTrade->setBuySell(TradeEnum::BASIC_TRADE_SELL_MODIFIER);
        $newBasicTrade->setValue($newValue);
        $newBasicTrade->setDate((int)round(microtime(true) * 1000));
        $newBasicTrade->setUniqId(uniqid());
        $newBasicTrade->setUserId($game->getUser()->getId());

        $this->basicTradeRepository->save($newBasicTrade);

        $storageManager->upperStorage(CommodityTypeConstants::COMMODITY_LATINUM, 1);
        $storageManager->lowerStorage($basicTrade->getCommodity()->getId(), $sellValue);

        $game->getInfo()->addInformation('Die Waren wurden verkauft');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
