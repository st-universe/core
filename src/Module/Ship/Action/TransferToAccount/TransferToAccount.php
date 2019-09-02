<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TransferToAccount;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use TradePost;

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
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        /**
         * @var TradePost $tradepost
         */
        $tradepost = ResourceCache()->getObject('tradepost', request::indInt('postid'));

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
        if (!$tradepost->userHasLicence($userId)) {
            return;
        }
        if ($tradepost->getStorageByUser($userId)->getStorageSum() >= $tradepost->getStorage()) {
            $game->addInformation(_('Dein Warenkonto an diesem Posten ist voll'));
            return;
        }
        $goods = request::postArray('goods');
        $gcount = request::postArray('count');

        $shipStorage = $ship->getStorage();

        if ($shipStorage === []) {
            $game->addInformation(_("Keine Waren zum Transferieren vorhanden"));
            return;
        }
        if (count($goods) == 0 || count($gcount) == 0) {
            $game->addInformation(_("Es wurde keine Waren zum Transferieren ausgewählt"));
            return;
        }
        $game->addInformation(_("Es wurden folgende Waren ins Warenkonto transferiert"));
        foreach ($goods as $key => $value) {
            $commodityId = (int) $value;
            if (!array_key_exists($key, $gcount)) {
                continue;
            }
            $storage = $shipStorage[$commodityId] ?? null;
            if ($storage === null) {
                continue;
            }
            $count = $gcount[$key];

            $commodity = $storage->getCommodity();

            if ($count == "m") {
                $count = $storage->getAmount();
            } else {
                $count = (int) $count;
            }
            if ($count < 1 || $tradepost->getStorage() - $tradepost->getStorageByUser($userId)->getStorageSum() <= 0) {
                continue;
            }
            if (!$commodity->isBeamable()) {
                $game->addInformationf(_('%s ist nicht beambar'), $commodity->getName());
                continue;
            }
            if ($commodity->isIllegal($tradepost->getTradeNetwork())) {
                $game->addInformationf(
                    _('Der Handel mit %s ist in diesem Handelsnetzwerk verboten'),
                    $commodity->getName()
                );
                continue;
            }
            $count = min($count, $storage->getAmount());
            if ($tradepost->getStorageByUser($userId)->getStorageSum() + $count > $tradepost->getStorage()) {
                $count = $tradepost->getStorage() - $tradepost->getStorageByUser($userId)->getStorageSum();
            }
            $game->addInformationf(_('%d %s'), $count, $commodity->getName());

            $ship->lowerStorage($commodityId, $count);
            $tradepost->upperStorage($userId, $value, $count);
            $tradepost->getStorageByUser($userId)->upperSum($count);
        }
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
