<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowShiplist;

use request;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;

final class ShowShiplist implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIPLIST';

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $tradePostId = request::getIntFatal('id');

        $game->setMacroInAjaxWindow('html/trademacros.xhtml/tradepostshiplist');
        $game->setPageTitle(_('Schiffe vor Ort'));

        $license = $this->tradeLicenseRepository->getLatestActiveLicenseByUserAndTradePost($userId, $tradePostId);

        if ($license === null) {
            throw new AccessViolation();
        }

        $station = $license->getTradePost()->getShip();

        $game->setTemplateVar('LIST', $this->shipRepository->getByLocationAndUser(
            $station->getCurrentMapField(),
            $game->getUser()
        ));
    }
}
