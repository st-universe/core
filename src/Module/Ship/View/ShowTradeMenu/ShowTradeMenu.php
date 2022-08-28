<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowTradeMenu;

use Stu\Exception\AccessViolation;
use request;
use Stu\Component\Game\GameEnum;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeCreateLicenceRepositoryInterface;

final class ShowTradeMenu implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TRADEMENU';

    private ShipLoaderInterface $shipLoader;

    private TradeLicenseRepositoryInterface $tradeLicenseRepository;

    private TradeCreateLicenceRepositoryInterface $tradeCreateLicenceRepository;

    private TradeLibFactoryInterface $tradeLibFactory;

    private TradePostRepositoryInterface $tradePostRepository;

    private PositionCheckerInterface $positionChecker;

    private CommodityRepositoryInterface $commodityRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeCreateLicenceRepositoryInterface $tradeCreateLicenceRepository,
        TradeLibFactoryInterface $tradeLibFactory,
        TradePostRepositoryInterface $tradePostRepository,
        CommodityRepositoryInterface $commodityRepository,
        PositionCheckerInterface $positionChecker
    ) {
        $this->shipLoader = $shipLoader;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeCreateLicenceRepository = $tradeCreateLicenceRepository;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->tradePostRepository = $tradePostRepository;
        $this->commodityRepository = $commodityRepository;
        $this->positionChecker = $positionChecker;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        /**
         * @var TradePostInterface $tradepost
         */
        $tradepost = $this->tradePostRepository->find((int) request::indInt('postid'));
        if ($tradepost === null) {
            return;
        }

        if (!$this->positionChecker->checkPosition($ship, $tradepost->getShip())) {
            new AccessViolation();
        }

        $game->setPageTitle(sprintf(_('Handelsposten: %s'), $tradepost->getName()));
        if (!in_array('noAjaxTemplate', $game->getViewContext())) {
            $game->setMacroInAjaxWindow('html/shipmacros.xhtml/trademenu');
        } else {
            $game->showMacro('html/shipmacros.xhtml/trademenu');
        }

        $databaseEntryId = $tradepost->getShip()->getDatabaseId();

        if ($databaseEntryId > 0) {
            $game->checkDatabaseItem($databaseEntryId);
        }
        $licenseInfo = $this->tradeCreateLicenceRepository->getLatestLicenseInfo($tradepost->getId());

        if ($licenseInfo !== null) {
            $commodityId = $licenseInfo->getGoodsId();
            $commodityName = $this->commodityRepository->find($commodityId)->getName();
            $licensecost = $licenseInfo->getAmount();
            $licensedays = $licenseInfo->getDays();
        } else {
            $commodityId = 1;
            $commodityName = 'Keine Ware';
            $licensecost = 0;
            $licensedays = 0;
        }

        $game->setTemplateVar('TRADEPOST', $this->tradeLibFactory->createTradeAccountTal($tradepost, $userId));
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar(
            'HAS_LICENSE',
            $this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, (int) $tradepost->getId())
        );
        $game->setTemplateVar(
            'CAN_BUY_LICENSE',
            $licenseInfo !== null
        );
        $game->setTemplateVar('LICENSEGOOD', $commodityId);
        $game->setTemplateVar('LICENSEGOODNAME', $commodityName);
        $game->setTemplateVar('LICENSECOST', $licensecost);
        $game->setTemplateVar('LICENSEDAYS', $licensedays);
    }
}