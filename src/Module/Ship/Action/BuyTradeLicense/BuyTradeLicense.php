<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\BuyTradeLicense;

use request;
use Ship;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowTradeMenu\ShowTradeMenu;
use TradeLicencesData;
use TradePost;
use TradeStorage;

final class BuyTradeLicense implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_PAY_TRADELICENCE';

    private $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTradeMenu::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        /**
         * @var TradePost $tradepost
         */
        $tradepost = ResourceCache()->getObject('tradepost', request::getIntFatal('postid'));

        if (!checkPosition($ship, $tradepost->getShip())) {
            return;
        }
        $targetId = request::getIntFatal('target');
        $mode = request::getStringFatal('method');

        if (!$tradepost->currentUserCanBuyLicence($userId)) {
            return;
        }

        if ($tradepost->userHasLicence($userId)) {
            return;
        }
        switch ($mode) {
            case 'ship':
                /** @var Ship $obj */
                $obj = ResourceCache()->getObject('ship', $targetId);
                if (!$obj->ownedByCurrentUser()) {
                    return;
                }
                if (!checkPosition($tradepost->getShip(), $obj)) {
                    return;
                }

                $commodityId = (int) $tradepost->getLicenceCostGood()->getId();

                $storage = $obj->getStorage()[$commodityId] ?? null;
                if ($storage === null || $storage->getAmount() < $tradepost->calculateLicenceCost()) {
                    return;
                }
                $obj->lowerStorage($commodityId, $tradepost->calculateLicenceCost());
                break;
            case 'account':
                $stor = TradeStorage::getStorageByGood($targetId, $userId,
                    $tradepost->getLicenceCostGood()->getId());
                if ($stor == 0) {
                    return;
                }
                if ($stor->getTradePost()->getTradeNetwork() != $tradepost->getTradeNetwork()) {
                    return;
                }
                $stor->getTradePost()->lowerStorage($userId, $tradepost->getLicenceCostGood()->getId(),
                    $tradepost->calculateLicenceCost());
                break;
            default:
                return;
        }
        $licence = new TradeLicencesData();
        $licence->setTradePostId($tradepost->getId());
        $licence->setUserId($userId);
        $licence->setDate(time());
        $licence->save();
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
