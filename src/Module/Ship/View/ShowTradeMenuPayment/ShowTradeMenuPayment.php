<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowTradeMenuPayment;

use Override;
use request;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseInfoRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class ShowTradeMenuPayment implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_TRADEMENU_CHOOSE_PAYMENT';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private TradeLicenseRepositoryInterface $tradeLicenseRepository,
        private TradeLicenseInfoRepositoryInterface $TradeLicenseInfoRepository,
        private TradeLibFactoryInterface $tradeLibFactory,
        private TradePostRepositoryInterface $tradePostRepository,
        private StorageRepositoryInterface $storageRepository,
        private ShipRepositoryInterface $shipRepository,
        private InteractionCheckerBuilderFactoryInterface $interactionCheckerBuilderFactory
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            false,
            false
        );
        $game->showMacro('html/entityNotAvailable.twig');

        $tradepost = $this->tradePostRepository->find(request::getIntFatal('postid'));
        if ($tradepost === null) {
            return;
        }

        if (!$this->interactionCheckerBuilderFactory
            ->createInteractionChecker()
            ->setSource($ship)
            ->setTarget($tradepost->getStation())
            ->setCheckTypes([
                InteractionCheckType::EXPECT_SOURCE_SUFFICIENT_CREW,
                InteractionCheckType::EXPECT_SOURCE_UNSHIELDED,
                InteractionCheckType::EXPECT_SOURCE_UNWARPED,
                InteractionCheckType::EXPECT_TARGET_UNWARPED,
                InteractionCheckType::EXPECT_TARGET_UNCLOAKED,
                InteractionCheckType::EXPECT_TARGET_UNSHIELDED
            ])
            ->check($game->getInfo())) {
            return;
        }

        $licenseInfo = $this->TradeLicenseInfoRepository->getLatestLicenseInfo($tradepost->getId());
        if ($licenseInfo === null) {
            $game->getInfo()->addInformation('Keine Lizenz verfügbar');
            return;
        }
        $commodity = $licenseInfo->getCommodity();
        $commodityId = $commodity->getId();
        $commodityName = $commodity->getName();
        $licenseCost = $licenseInfo->getAmount();

        $game->showMacro('html/ship/trademenupayment.twig');

        $game->setTemplateVar('TRADEPOST', $this->tradeLibFactory->createTradeAccountWrapper($tradepost, $userId));
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('LICENSECOMMODITY', $commodityId);
        $game->setTemplateVar('LICENSECOMMODITYNAME', $commodityName);
        $game->setTemplateVar('LICENSECOST', $licenseCost);
        $game->setTemplateVar('LICENSEDAYS', $licenseInfo->getDays());

        if (
            !$this->tradeLicenseRepository->hasLicenseByUserAndTradePost($userId, $tradepost->getId())
        ) {
            $game->setTemplateVar(
                'DOCKED_SHIPS_FOR_LICENSE',
                $this->shipRepository->getWithTradeLicensePayment(
                    $userId,
                    $tradepost->getStationId(),
                    $commodityId,
                    $licenseCost
                )
            );

            $game->setTemplateVar(
                'ACCOUNTS_FOR_LICENSE',
                $this->storageRepository->getByTradeNetworkAndUserAndCommodityAmount(
                    $tradepost->getTradeNetwork(),
                    $userId,
                    $commodityId,
                    $licenseCost
                )
            );
        }
    }
}
