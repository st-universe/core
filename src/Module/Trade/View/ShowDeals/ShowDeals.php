<?php

declare(strict_types=1);

namespace Stu\Module\Trade\View\ShowDeals;

use Override;
use Stu\Lib\Pirate\Component\PirateWrathManager;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;

final class ShowDeals implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_DEALS';

    public function __construct(
        private DealsRepositoryInterface $dealsRepository,
        private StuTime $stuTime,
        private TradeLicenseRepositoryInterface $tradeLicenseRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $game->appendNavigationPart(
            'trade.php',
            _('Handel')
        );
        $game->appendNavigationPart(
            sprintf('trade.php?%s=1', self::VIEW_IDENTIFIER),
            _('Deals')
        );
        $game->setPageTitle(_('/ Handel / Deals des Großen Nagus'));
        $game->setViewTemplate('html/trade/deals.twig');

        $user->setDeals(false);

        $hasLicense = $this->tradeLicenseRepository->hasFergLicense($userId);

        $game->setTemplateVar('HAS_LICENSE', $hasLicense);
        if (!$hasLicense) {
            return;
        }

        $time = $this->stuTime->time();
        $pirateWrath = $user->getPirateWrath();
        if ($pirateWrath === null) {
            $game->setTemplateVar('WRATH', PirateWrathManager::DEFAULT_WRATH);
            $game->setTemplateVar('PROTECTIONTIMEOUT', $time);
        } else {
            $game->setTemplateVar('WRATH', $pirateWrath->getWrath());
            $protectionTimeout = $pirateWrath->getProtectionTimeout();
            $game->setTemplateVar('PROTECTIONTIMEOUT', $protectionTimeout);

            if ($protectionTimeout !== null && $pirateWrath->getProtectionTimeout() < $time) {
                $game->setTemplateVar('PROTECTIONTIME', $this->stuTime->transformToStuDateTime($protectionTimeout));
            }
        }


        $hasActivedeals = $this->dealsRepository->hasActiveDeals($userId);
        if ($hasActivedeals) {
            $this->loadActiveDeals($userId, $game);
        }

        //load active auctions
        $hasActiveAuctions = $this->dealsRepository->hasActiveAuctions($userId);
        if ($hasActiveAuctions) {
            $this->loadActiveAuctions($userId, $game);
        }

        // load auctions to take
        $hasOwnAuctionsToTake = $this->dealsRepository->hasOwnAuctionsToTake($userId);
        if ($hasOwnAuctionsToTake) {
            $this->loadOwnAuctionsToTake($userId, $game);
        }

        $hasEndedAuctions = $this->dealsRepository->hasEndedAuctions($userId);
        if ($hasEndedAuctions) {
            $this->loadEndedAuctions($userId, $game);
        }

        $game->setTemplateVar('HASACTIVEDEALS', $hasActivedeals);
        $game->setTemplateVar('HASACTIVEAUCTIONS', $hasActiveAuctions);
        $game->setTemplateVar('HASOWNENDEDAUCTIONS', $hasOwnAuctionsToTake);
        $game->setTemplateVar('HASENDEDAUCTIONS', $hasEndedAuctions);
    }


    private function loadActiveDeals(int $userId, GameControllerInterface $game): void
    {
        $activedealsgoods = $this->dealsRepository->getActiveDealsGoods($userId);
        $activedealsships = $this->dealsRepository->getActiveDealsShips($userId);
        $activedealsbuildplans = $this->dealsRepository->getActiveDealsBuildplans($userId);
        $activedealsgoodsprestige = $this->dealsRepository->getActiveDealsGoodsPrestige($userId);
        $activedealsshipsprestige = $this->dealsRepository->getActiveDealsShipsPrestige($userId);
        $activedealsbuildplansprestige = $this->dealsRepository->getActiveDealsBuildplansPrestige($userId);

        $game->setTemplateVar('ACTIVEDEALSGOODS', $activedealsgoods);
        $game->setTemplateVar('ACTIVEDEALSSHIPS', $activedealsships);
        $game->setTemplateVar('ACTIVEDEALSBUILDPLANS', $activedealsbuildplans);
        $game->setTemplateVar('ACTIVEDEALSGOODSPRESTIGE', $activedealsgoodsprestige);
        $game->setTemplateVar('ACTIVEDEALSSHIPSPRESTIGE', $activedealsshipsprestige);
        $game->setTemplateVar('ACTIVEDEALSBUILDPLANSPRESTIGE', $activedealsbuildplansprestige);
    }

    private function loadActiveAuctions(int $userId, GameControllerInterface $game): void
    {
        $activeauctionsgoods = $this->dealsRepository->getActiveAuctionsGoods($userId);
        $activeauctionsships = $this->dealsRepository->getActiveAuctionsShips($userId);
        $activeauctionsbuildplans = $this->dealsRepository->getActiveAuctionsBuildplans($userId);
        $activeauctionsgoodsprestige = $this->dealsRepository->getActiveAuctionsGoodsPrestige($userId);
        $activeauctionsshipsprestige = $this->dealsRepository->getActiveAuctionsShipsPrestige($userId);
        $activeauctionsbuildplansprestige = $this->dealsRepository->getActiveAuctionsBuildplansPrestige($userId);

        $game->setTemplateVar('ACTIVEAUCTIONSGOODS', $activeauctionsgoods);
        $game->setTemplateVar('ACTIVEAUCTIONSSHIPS', $activeauctionsships);
        $game->setTemplateVar('ACTIVEAUCTIONSBUILDPLANS', $activeauctionsbuildplans);
        $game->setTemplateVar('ACTIVEAUCTIONSGOODSPRESTIGE', $activeauctionsgoodsprestige);
        $game->setTemplateVar('ACTIVEAUCTIONSSHIPSPRESTIGE', $activeauctionsshipsprestige);
        $game->setTemplateVar('ACTIVEAUCTIONSBUILDPLANSPRESTIGE', $activeauctionsbuildplansprestige);
    }

    private function loadOwnAuctionsToTake(int $userId, GameControllerInterface $game): void
    {
        $ownendedauctionsgoods = $this->dealsRepository->getOwnEndedAuctionsGoods($userId);
        $ownendedauctionsships = $this->dealsRepository->getOwnEndedAuctionsShips($userId);
        $ownendedauctionsbuildplans = $this->dealsRepository->getOwnEndedAuctionsBuildplans($userId);
        $ownendedauctionsgoodsprestige = $this->dealsRepository->getOwnEndedAuctionsGoodsPrestige($userId);
        $ownendedauctionsshipsprestige = $this->dealsRepository->getOwnEndedAuctionsShipsPrestige($userId);
        $ownendedauctionsbuildplansprestige = $this->dealsRepository->getOwnEndedAuctionsBuildplansPrestige($userId);

        $game->setTemplateVar('OWNENDEDAUCTIONSGOODS', $ownendedauctionsgoods);
        $game->setTemplateVar('OWNENDEDAUCTIONSSHIPS', $ownendedauctionsships);
        $game->setTemplateVar('OWNENDEDAUCTIONSBUILDPLANS', $ownendedauctionsbuildplans);
        $game->setTemplateVar('OWNENDEDAUCTIONSGOODSPRESTIGE', $ownendedauctionsgoodsprestige);
        $game->setTemplateVar('OWNENDEDAUCTIONSSHIPSPRESTIGE', $ownendedauctionsshipsprestige);
        $game->setTemplateVar('OWNENDEDAUCTIONSBUILDPLANSPRESTIGE', $ownendedauctionsbuildplansprestige);
    }

    private function loadEndedAuctions(int $userId, GameControllerInterface $game): void
    {
        $endedauctionsgoods = $this->dealsRepository->getEndedAuctionsGoods($userId);
        $endedauctionsships = $this->dealsRepository->getEndedAuctionsShips($userId);
        $endedauctionsbuildplans = $this->dealsRepository->getEndedAuctionsBuildplans($userId);
        $endedauctionsgoodsprestige = $this->dealsRepository->getEndedAuctionsGoodsPrestige($userId);
        $endedauctionsshipsprestige = $this->dealsRepository->getEndedAuctionsShipsPrestige($userId);
        $endedauctionsbuildplansprestige = $this->dealsRepository->getEndedAuctionsBuildplansPrestige($userId);

        $game->setTemplateVar('ENDEDAUCTIONSGOODS', $endedauctionsgoods);
        $game->setTemplateVar('ENDEDAUCTIONSSHIPS', $endedauctionsships);
        $game->setTemplateVar('ENDEDAUCTIONSBUILDPLANS', $endedauctionsbuildplans);
        $game->setTemplateVar('ENDEDAUCTIONSGOODSPRESTIGE', $endedauctionsgoodsprestige);
        $game->setTemplateVar('ENDEDAUCTIONSSHIPSPRESTIGE', $endedauctionsshipsprestige);
        $game->setTemplateVar('ENDEDAUCTIONSBUILDPLANSPRESTIGE', $endedauctionsbuildplansprestige);
    }
}
