<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowTradeMenu;

use AccessViolation;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use TradePost;

final class ShowTradeMenu implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TRADEMENU';

    private $shipLoader;

    private $tradeLicenseRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        TradeLicenseRepositoryInterface $tradeLicenseRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
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
            new AccessViolation();
        }

        $game->setPageTitle(sprintf(_('Handelsposten: %s'), $tradepost->getName()));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/shipmacros.xhtml/trademenu');

        $databaseEntryId = $tradepost->getShip()->getDatabaseId();

        if ($databaseEntryId > 0) {
            $game->checkDatabaseItem($databaseEntryId);
        }

        $game->setTemplateVar('TRADEPOST', $tradepost);
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar(
            'HAS_LICENSE',
            $this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, (int) $tradepost->getId())
        );
        $game->setTemplateVar(
            'CAN_BUY_LICENSE',
            $this->tradeLicenseRepository->getAmountByUser($userId) < MAX_TRADELICENCE_COUNT
        );
    }
}
