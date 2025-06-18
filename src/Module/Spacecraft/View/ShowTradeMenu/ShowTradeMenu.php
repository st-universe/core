<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowTradeMenu;

use Override;
use request;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseInfoRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShowTradeMenu implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TRADEMENU';

    /**
     * @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spaceCraftLoader
     */
    public function __construct(
        private TradeLicenseRepositoryInterface $tradeLicenseRepository,
        private TradeLicenseInfoRepositoryInterface $TradeLicenseInfoRepository,
        private TradeLibFactoryInterface $tradeLibFactory,
        private TradePostRepositoryInterface $tradePostRepository,
        private CommodityRepositoryInterface $commodityRepository,
        private InteractionCheckerInterface $interactionChecker,
        private SpacecraftLoaderInterface $spaceCraftLoader
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $spacecraft = $this->spaceCraftLoader->getByIdAndUser(request::getIntFatal('id'), $userId);

        $tradepost = $this->tradePostRepository->find(request::indInt('postid'));
        if ($tradepost === null) {
            return;
        }

        if (!$this->interactionChecker->checkPosition($spacecraft, $tradepost->getStation())) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Handelstransfermenü'));
        if ($game->getViewContext(ViewContextTypeEnum::NO_AJAX) === true) {
            $game->showMacro('html/spacecraft/trademenu.twig');
        } else {
            $game->setMacroInAjaxWindow('html/spacecraft/trademenu.twig');
        }

        $databaseEntryId = $tradepost->getStation()->getDatabaseId();

        if ($databaseEntryId > 0) {
            $game->checkDatabaseItem($databaseEntryId);
        }
        $licenseInfo = $this->TradeLicenseInfoRepository->getLatestLicenseInfo($tradepost->getId());

        if ($licenseInfo !== null) {
            $commodityId = $licenseInfo->getCommodityId();
            $commodity = $this->commodityRepository->find($commodityId);
            if ($commodity !== null) {
                $commodityName = $commodity->getName();
            } else {
                $commodityName = '';
            }
            $licensecost = $licenseInfo->getAmount();
            $licensedays = $licenseInfo->getDays();
        } else {
            $commodityId = 1;
            $commodityName = 'Keine Ware';
            $licensecost = 0;
            $licensedays = 0;
        }

        $game->setTemplateVar('TRADEPOST', $this->tradeLibFactory->createTradeAccountWrapper($tradepost, $userId));
        $game->setTemplateVar('SHIP', $spacecraft);
        $game->setTemplateVar(
            'HAS_LICENSE',
            $this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, $tradepost->getId())
        );
        $game->setTemplateVar(
            'CAN_BUY_LICENSE',
            $licenseInfo !== null
        );
        $game->setTemplateVar('LICENSECOMMODITY', $commodityId);
        $game->setTemplateVar('LICENSECOMMODITYNAME', $commodityName);
        $game->setTemplateVar('LICENSECOST', $licensecost);
        $game->setTemplateVar('LICENSEDAYS', $licensedays);
    }
}
