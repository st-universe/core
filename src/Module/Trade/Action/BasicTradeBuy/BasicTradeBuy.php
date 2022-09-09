<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\BasicTradeBuy;

use request;
use Stu\Component\Trade\TradeEnum;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Trade\Lib\BasicTradeItem;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowBasicTrade\ShowBasicTrade;
use Stu\Orm\Repository\BasicTradeRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class BasicTradeBuy implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_BASIC_BUY';

    private TradeLibFactoryInterface $tradeLibFactory;

    private BasicTradeRepositoryInterface $basicTradeRepository;

    private TradePostRepositoryInterface $tradePostRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        TradeLibFactoryInterface $tradeLibFactory,
        BasicTradeRepositoryInterface $basicTradeRepository,
        TradePostRepositoryInterface $tradePostRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->tradeLibFactory = $tradeLibFactory;
        $this->basicTradeRepository = $basicTradeRepository;
        $this->tradePostRepository = $tradePostRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        if ($game->getUser()->getId() === 126) {
            $this->loggerUtil->init('trade', LoggerEnum::LEVEL_ERROR);
        }
        $game->setView(ShowBasicTrade::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $tradePostId = request::postIntFatal('tpid');
        $uniqId = request::postStringFatal('uid');
        $basicTrade = $this->basicTradeRepository->getByUniqId($uniqId);

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

        $storageManager = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $userId);

        if ($storageManager->getFreeStorage() <= 0) {
            $game->addInformation("Dein Warenkonto auf diesem Handelsposten ist überfüllt - es konnte nicht gekauft werden");
            return;
        }

        $latinumStorage = $storageManager->getStorage()[CommodityTypeEnum::GOOD_LATINUM];

        if ($latinumStorage === null || $latinumStorage < 1) {
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

        $this->loggerUtil->log(sprintf('value: %d, amount: %d', $basicTrade->getValue(), $amount));

        $storageManager->upperStorage($basicTrade->getCommodity()->getId(), $amount);
        $storageManager->lowerStorage(CommodityTypeEnum::GOOD_LATINUM, 1);

        $game->addInformation('Die Waren wurden gekauft');
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
