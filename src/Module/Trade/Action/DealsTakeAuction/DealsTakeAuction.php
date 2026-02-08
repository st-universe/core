<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsTakeAuction;

use RuntimeException;
use Stu\Component\Spacecraft\ModuleSpecialAbilityEnum;
use Stu\Component\Trade\TradeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowDeals\ShowDeals;
use Stu\Orm\Entity\AuctionBid;
use Stu\Orm\Entity\Deals;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class DealsTakeAuction implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DEALS_TAKE_AUCTION';

    public function __construct(private DealsTakeAuctionRequestInterface $dealstakeAuctionRequest, private TradeLibFactoryInterface $tradeLibFactory, private DealsRepositoryInterface $dealsRepository, private TradePostRepositoryInterface $tradepostRepository, private TradeLicenseRepositoryInterface $tradeLicenseRepository, private BuildplanModuleRepositoryInterface $buildplanModuleRepository, private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository, private ShipCreatorInterface $shipCreator, private CreatePrestigeLogInterface $createPrestigeLog, private StuTime $stuTime) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $user = $game->getUser();
        $dealId = $this->dealstakeAuctionRequest->getDealId();
        $game->setView(ShowDeals::VIEW_IDENTIFIER);

        $auction = $this->dealsRepository->find($dealId);

        if ($auction === null) {
            $game->getInfo()->addInformation(_('Das Angebot ist nicht mehr verfügbar'));
            return;
        }

        if ($userId < 100) {
            $game->getInfo()->addInformation(_('NPCs können dieses Angebot nicht annehmen'));
            return;
        }

        // sanity checks
        if ($auction->getTakenTime() !== null) {
            return;
        }
        if ($auction->getAuctionUser()?->getId() !== $user->getId()) {
            return;
        }

        if (!$this->tradeLicenseRepository->hasFergLicense($userId)) {
            throw new AccessViolationException(sprintf(
                _('UserId %d does not have license for Deals'),
                $userId
            ));
        }

        $highestBid = $auction->getHighestBid();
        if ($highestBid === null) {
            throw new RuntimeException('no highest bid present');
        }

        $neededStorageSpace = $this->determineNeededStorageSpace($auction, $highestBid);
        $tradePost = $this->tradepostRepository->find(TradeEnum::DEALS_FERG_TRADEPOST_ID);
        if ($tradePost === null) {
            throw new RuntimeException('no deals ferg tradepost found');
        }
        $storageManagerUser = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $user);
        $freeStorage = $storageManagerUser->getFreeStorage();

        //check if enough space in storage

        if ($neededStorageSpace > $freeStorage) {
            /*$game->getInfo()->addInformationf(_('Dein Warenkonto auf diesem Handelsposten ist zu voll, es wird %d freier Lagerraum benötigt'), $neededStorageSpace);

            return; */
        }

        $currentBidAmount = $auction->getAuctionAmount();
        $currentMaxAmount = $highestBid->getMaxAmount();

        //give overpay back
        if ($auction->getAuctionAmount() < $currentMaxAmount) {
            //give prestige back

            $wantedCommodity = $auction->getWantedCommodity();
            if ($wantedCommodity === null) {
                $description = sprintf(
                    '%d Prestige: Du hast Prestige bei einer Auktion zurückerhalten, weil dein Maximalgebot über dem Höchstgebot lag',
                    $currentMaxAmount - $currentBidAmount
                );
                $this->createPrestigeLog->createLog($currentMaxAmount - $currentBidAmount, $description, $user, time());
                $game->getInfo()->addInformation(sprintf(
                    _('Dir wurden %d Prestige gutgeschrieben'),
                    $currentMaxAmount - $currentBidAmount,
                ));
            } elseif ($freeStorage < ($currentMaxAmount - $currentBidAmount)) {
                $game->getInfo()->addInformation(sprintf(
                    _('Es befindet sich nicht genügend Platz für die Rückerstattung von %d %s diesem Handelsposten'),
                    $currentMaxAmount - $currentBidAmount,
                    $wantedCommodity->getName()
                ));
                return;
            } else {
                $storageManagerUser->upperStorage(
                    $wantedCommodity->getId(),
                    $currentMaxAmount - $currentBidAmount
                );
                $game->getInfo()->addInformation(sprintf(
                    _('Dir wurden %d %s auf diesem Handelsposten gutgeschrieben'),
                    $currentMaxAmount - $currentBidAmount,
                    $wantedCommodity->getName()
                ));
            }
        }

        $givenCommodity = $auction->getGiveCommodity();
        if ($givenCommodity !== null) {
            $storageManagerUser->upperStorage(
                $givenCommodity->getId(),
                (int) $auction->getGiveCommodityAmount()
            );

            $game->getInfo()->addInformation(sprintf(_('Du hast %d %s erhalten'), (int) $auction->getGiveCommodityAmount(), $givenCommodity->getName()));
        }

        $buildplan = $auction->getBuildplan();
        if ($buildplan !== null) {
            if ($auction->getShip() == true) {
                $this->createShip($buildplan, $tradePost, $userId);
                $game->getInfo()->addInformation(_('Du hast dein Schiff erhalten'));
            }

            if ($auction->getShip() == false) {
                $this->copyBuildplan($buildplan, $user);

                $game->getInfo()->addInformation(_('Du hast deinen Bauplan erhalten'));
            }
        }

        $auction->setTakenTime($this->stuTime->time());
        $this->dealsRepository->save($auction);
    }

    private function determineNeededStorageSpace(Deals $auction, AuctionBid $highestBid): int
    {
        $result = 0;

        if ($auction->getGiveCommodityId() !== null) {
            $result += $auction->getGiveCommodityAmount();
        }

        if ($auction->getAuctionAmount() < $highestBid->getMaxAmount()) {
            $result += $highestBid->getMaxAmount() - $auction->getAuctionAmount();
        }

        return $result;
    }


    private function createShip(SpacecraftBuildplan $buildplan, TradePost $tradePost, int $userId): void
    {
        $this->shipCreator->createBy($userId, $buildplan->getRump()->getId(), $buildplan->getId())
            ->setLocation($tradePost->getStation()->getLocation())
            ->loadEps(25)
            ->loadReactor(25)
            ->loadWarpdrive(25)
            ->finishConfiguration();
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }

    private function copyBuildplan(SpacecraftBuildplan $buildplan, User $user): void
    {
        //copying buildplan
        $newPlan = $this->spacecraftBuildplanRepository->prototype();
        $newPlan->setUser($user);
        $newPlan->setRump($buildplan->getRump());
        $newPlan->setName($buildplan->getName());
        $newPlan->setSignature($buildplan->getSignature());
        $newPlan->setBuildtime($buildplan->getBuildtime());
        $newPlan->setCrew($buildplan->getCrew());

        $this->spacecraftBuildplanRepository->save($newPlan);

        //copying buildplan modules
        foreach ($buildplan->getModulesOrdered() as $buildplanModule) {
            $mod = $this->buildplanModuleRepository->prototype();
            $mod->setModuleType($buildplanModule->getModule()->getType());
            $mod->setBuildplan($newPlan);
            $mod->setModule($buildplanModule->getModule());
            $mod->setModuleSpecial(ModuleSpecialAbilityEnum::getHash($buildplanModule->getModule()->getSpecials()));
            $this->buildplanModuleRepository->save($mod);
        }
    }
}
