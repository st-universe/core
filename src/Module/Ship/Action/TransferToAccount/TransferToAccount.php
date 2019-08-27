<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TransferToAccount;

use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowTradeMenu\ShowTradeMenu;

final class TransferToAccount implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TRANSFER_TO_ACCOUNT';

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
         * @var \TradePost $tradepost
         */
        $tradepost = ResourceCache()->getObject('tradepost', request::getIntFatal('postid'));

        if (!checkPosition($ship, $tradepost->getShip())) {
            return;
        }

        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->getWarpState()) {
            $game->addInformation(_("Der Warpantrieb ist aktiviert"));
            return;
        }
        if (!$tradepost->currentUserHasLicence()) {
            return;
        }
        if ($tradepost->getStorageByUser(currentUser()->getId())->getStorageSum() >= $tradepost->getStorage()) {
            $game->addInformation(_('Dein Warenkonto an diesem Posten ist voll'));
            return;
        }
        $goods = request::postArray('goods');
        $gcount = request::postArray('count');
        if ($ship->getStorage()->count() == 0) {
            $game->addInformation(_("Keine Waren zum Transferieren vorhanden"));
            return;
        }
        if (count($goods) == 0 || count($gcount) == 0) {
            $game->addInformation(_("Es wurde keine Waren zum Transferieren ausgewählt"));
            return;
        }
        $game->addInformation(_("Es wurden folgende Waren ins Warenkonto transferiert"));
        foreach ($goods as $key => $value) {
            if (!array_key_exists($key, $gcount)) {
                continue;
            }
            if (!$ship->getStorage()->offsetExists($value)) {
                continue;
            }
            $count = $gcount[$key];
            $good = $ship->getStorage()->offsetGet($value);
            if ($count == "m") {
                $count = $good->getAmount();
            } else {
                $count = intval($count);
            }
            if ($count < 1 || $tradepost->getStorage() - $tradepost->getStorageByUser(currentUser()->getId())->getStorageSum() <= 0) {
                continue;
            }
            if (!$good->getGood()->isBeamable()) {
                $game->addInformation(sprintf(_('%s ist nicht beambar'), $good->getGood()->getName()));
                $game->addInformation($good->getGood()->getName() . " ist nicht beambar");
                continue;
            }
            if ($good->getGood()->isIllegal($tradepost->getTradeNetwork())) {
                $game->addInformation(sprintf(_('Der Handel mit %s ist in diesem Handelsnetzwerk verboten'),
                    $good->getGood()->getName()));
                continue;
            }
            if ($count > $good->getAmount()) {
                $count = $good->getAmount();
            }
            if ($tradepost->getStorageByUser(currentUser()->getId())->getStorageSum() + $count > $tradepost->getStorage()) {
                $count = $tradepost->getStorage() - $tradepost->getStorageByUser(currentUser()->getId())->getStorageSum();
            }
            $game->addInformation(sprintf(_('%d %s'), $count, $good->getGood()->getName()));

            $ship->lowerStorage($value, $count);
            $tradepost->upperStorage(currentUser()->getId(), $value, $count);
            $tradepost->getStorageByUser(currentUser()->getId())->upperSum($count);
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
