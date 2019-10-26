<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\Overview;

use Stu\Component\Game\GameEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Trade\Lib\TradeOfferItem;
use Stu\Module\Trade\Lib\TradeOfferItemInterface;
use Stu\Orm\Entity\TradeOfferInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradeOfferRepositoryInterface;

final class Overview implements ViewControllerInterface
{
    private $tradeLicenseRepository;

    private $tradeOfferRepository;

    public function __construct(
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeOfferRepositoryInterface $tradeOfferRepository
    ) {
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeOfferRepository = $tradeOfferRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $game->appendNavigationPart(
            'trade.php',
            _('Handel')
        );
        $game->setPageTitle(_('/ Handel'));
        $game->setTemplateFile('html/trade.xhtml');

        $game->setTemplateVar(
            'TRADE_LICENSE_COUNT',
            $this->tradeLicenseRepository->getAmountByUser($userId)
        );
        $game->setTemplateVar('MAX_TRADE_LICENSE_COUNT', GameEnum::MAX_TRADELICENCE_COUNT);
        $game->setTemplateVar(
            'OFFER_LIST',
            array_map(
                function (TradeOfferInterface $tradeOffer) use ($user): TradeOfferItemInterface {
                    return new TradeOfferItem($tradeOffer, $user);
                },
                $this->tradeOfferRepository->getByUserLicenses($userId)
            )
        );
    }
}
