<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\BasicTradeBuy;

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

final class BasicTradeBuy implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_BASIC_BUY';

    public function __construct(private TradeLibFactoryInterface $tradeLibFactory, private BasicTradeRepositoryInterface $basicTradeRepository, private TradePostRepositoryInterface $tradePostRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowBasicTrade::VIEW_IDENTIFIER);

        $tradePostId = request::postIntFatal('tpid');
        $uniqId = request::postStringFatal('uid');
        $basicTrade = $this->basicTradeRepository->getByUniqId($uniqId);

        if ($game->getUser()->getId() < 100) {
            $game->addInformation(_('NPCs können dieses Angebot nicht annehmen'));
            return;
        }

        if ($basicTrade === null) {
            return;
        }

        $isNewest = $this->basicTradeRepository->isNewest($basicTrade);

        if (!$isNewest) {
            $game->addInformation("Kurs wurde zwischenzeitlich aktualisiert - es konnte nicht gekauft werden");
            return;
        }

        $tradePost = $this->tradePostRepository->find($tradePostId);

        if ($tradePost === null) {
            return;
        }

        $storageManager = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $game->getUser());

        if ($storageManager->getFreeStorage() <= 0) {
            $game->addInformation("Dein Warenkonto auf diesem Handelsposten ist überfüllt - es konnte nicht gekauft werden");

            return;
        }

        /** @var ?Storage */
        $latinumStorage = $storageManager->getStorage()->get(CommodityTypeConstants::COMMODITY_LATINUM);

        if ($latinumStorage === null || $latinumStorage->getAmount() < 1) {
            $game->addInformation("Dein Warenkonto verfügt über kein Latinum - es konnte nicht gekauft werden");

            return;
        }

        $latestRates = $this->basicTradeRepository->getLatestRates($basicTrade);

        $newValue = null;
        $summand = 1;

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
        $newBasicTrade->setBuySell(TradeEnum::BASIC_TRADE_BUY_MODIFIER);
        $newBasicTrade->setValue($newValue);
        $newBasicTrade->setDate((int)round(microtime(true) * 1000));
        $newBasicTrade->setUniqId(uniqid());
        $newBasicTrade->setUserId($game->getUser()->getId());

        $this->basicTradeRepository->save($newBasicTrade);

        $amount = (int) ($basicTrade->getValue() / BasicTradeItem::BASIC_TRADE_VALUE_SCALE);

        $storageManager->upperStorage($basicTrade->getCommodity()->getId(), $amount);
        $storageManager->lowerStorage(CommodityTypeConstants::COMMODITY_LATINUM, 1);

        $game->addInformation('Die Waren wurden gekauft');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
