<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowShiplist;

use Override;
use request;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;

final class ShowShiplist implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHIPLIST';

    public function __construct(private TradeLicenseRepositoryInterface $tradeLicenseRepository, private ShipRepositoryInterface $shipRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $tradePostId = request::getIntFatal('id');

        $game->setMacroInAjaxWindow('html/trade/tradePostShipList.twig');
        $game->setPageTitle(_('Schiffe vor Ort'));

        $license = $this->tradeLicenseRepository->getLatestActiveLicenseByUserAndTradePost($userId, $tradePostId);

        if ($license === null) {
            throw new AccessViolation();
        }

        $station = $license->getTradePost()->getShip();

        $game->setTemplateVar('LIST', $this->shipRepository->getByLocationAndUser(
            $station->getLocation(),
            $game->getUser()
        ));
    }
}
