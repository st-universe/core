<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsTakeAuction;

use Override;
use Stu\Component\Trade\TradeEnum;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\ShipModule\ModuleSpecialAbilityEnum;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowDeals\ShowDeals;
use Stu\Orm\Entity\DealsInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class DealsTakeAuction implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DEALS_TAKE_AUCTION';

    public function __construct(private DealsTakeAuctionRequestInterface $dealstakeAuctionRequest, private TradeLibFactoryInterface $tradeLibFactory, private DealsRepositoryInterface $dealsRepository, private TradePostRepositoryInterface $tradepostRepository, private TradeLicenseRepositoryInterface $tradeLicenseRepository, private BuildplanModuleRepositoryInterface $buildplanModuleRepository, private ShipBuildplanRepositoryInterface $shipBuildplanRepository, private ShipCreatorInterface $shipCreator, private CreatePrestigeLogInterface $createPrestigeLog, private StuTime $stuTime) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $user = $game->getUser();
        $dealId = $this->dealstakeAuctionRequest->getDealId();
        $game->setView(ShowDeals::VIEW_IDENTIFIER);

        $auction = $this->dealsRepository->find($dealId);

        if ($auction === null) {
            $game->addInformation(_('Das Angebot ist nicht mehr verfügbar'));
            return;
        }

        if ($userId < 100) {
            $game->addInformation(_('NPCs können dieses Angebot nicht annehmen'));
            return;
        }

        // sanity checks
        if ($auction->getTakenTime() !== null) {
            return;
        }
        if ($auction->getAuctionUser()->getId() !== $userId) {
            return;
        }


        if (!$this->tradeLicenseRepository->hasFergLicense($userId)) {
            throw new AccessViolation(sprintf(
                _('UserId %d does not have license for Deals'),
                $userId
            ));
        }

        // $neededStorageSpace = $this->determineNeededStorageSpace($auction);
        $tradePost = $this->tradepostRepository->getFergTradePost(TradeEnum::DEALS_FERG_TRADEPOST_ID);
        $storageManagerUser = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $user);
        $freeStorage = $storageManagerUser->getFreeStorage();

        //check if enough space in storage
        /*
        if ($neededStorageSpace > $freeStorage) {
            $game->addInformationf(_('Dein Warenkonto auf diesem Handelsposten ist zu voll, es wird %d freier Lagerraum benötigt'), $neededStorageSpace);
            return;
        }
            */

        $currentBidAmount = $auction->getAuctionAmount();
        $currentMaxAmount = $auction->getHighestBid()->getMaxAmount();

        //give overpay back
        if ($auction->getAuctionAmount() < $currentMaxAmount) {
            //give prestige back
            if ($auction->isPrestigeCost()) {
                $description = sprintf(
                    '%d Prestige: Du hast Prestige bei einer Auktion zurückerhalten, weil dein Maximalgebot über dem Höchstgebot lag',
                    $currentMaxAmount - $currentBidAmount
                );
                $this->createPrestigeLog->createLog($currentMaxAmount - $currentBidAmount, $description, $user, time());
                $game->addInformation(sprintf(
                    _('Dir wurden %d Prestige gutgeschrieben'),
                    $currentMaxAmount - $currentBidAmount,
                ));
            } elseif ($freeStorage < ($currentMaxAmount - $currentBidAmount)) {
                $game->addInformation(sprintf(
                    _('Es befindet sich nicht genügend Platz für die Rückerstattung von %d %s diesem Handelsposten'),
                    $currentMaxAmount - $currentBidAmount,
                    $auction->getWantedCommodity()->getName()
                ));
                return;
            } else {
                $storageManagerUser->upperStorage(
                    $auction->getwantCommodityId(),
                    $currentMaxAmount - $currentBidAmount
                );
                $game->addInformation(sprintf(
                    _('Dir wurden %d %s auf diesem Handelsposten gutgeschrieben'),
                    $currentMaxAmount - $currentBidAmount,
                    $auction->getWantedCommodity()->getName()
                ));
            }
        }

        if ($auction->getgiveCommodityId() !== null) {
            $storageManagerUser->upperStorage(
                $auction->getgiveCommodityId(),
                (int) $auction->getgiveCommodityAmount()
            );

            $game->addInformation(sprintf(_('Du hast %d %s erhalten'), (int) $auction->getgiveCommodityAmount(), $auction->getgiveCommodity()->getName()));
        }

        if ($auction->getShip() == true) {
            $this->createShip($auction->getBuildplan(), $tradePost, $userId);
            $game->addInformation(_('Du hast dein Schiff erhalten'));
        }

        if ($auction->getShip() == false && $auction->getBuildplanId() !== null) {
            $this->copyBuildplan($auction->getBuildplan(), $user);

            $game->addInformation(_('Du hast deinen Bauplan erhalten'));
        }

        $auction->setTakenTime($this->stuTime->time());
        $this->dealsRepository->save($auction);
    }

    private function determineNeededStorageSpace(DealsInterface $auction): int
    {
        $result = 0;

        if ($auction->getgiveCommodityId() !== null) {
            $result += $auction->getgiveCommodityAmount();
        }

        if ($auction->getAuctionAmount() < $auction->getHighestBid()->getMaxAmount()) {
            $result += $auction->getHighestBid()->getMaxAmount() - $auction->getAuctionAmount();
        }

        return $result;
    }


    private function createShip(ShipBuildplanInterface $buildplan, TradePostInterface $tradePost, int $userId): void
    {
        $this->shipCreator->createBy($userId, $buildplan->getRump()->getId(), $buildplan->getId())
            ->setLocation($tradePost->getShip()->getLocation())
            ->loadEps(25)
            ->loadReactor(25)
            ->loadWarpdrive(25)
            ->finishConfiguration();
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }

    private function copyBuildplan(ShipBuildplanInterface $buildplan, UserInterface $user): void
    {
        //copying buildplan
        $newPlan = $this->shipBuildplanRepository->prototype();
        $newPlan->setUser($user);
        $newPlan->setRump($buildplan->getRump());
        $newPlan->setName($buildplan->getName());
        $newPlan->setSignature($buildplan->getSignature());
        $newPlan->setBuildtime($buildplan->getBuildtime());
        $newPlan->setCrew($buildplan->getCrew());

        $this->shipBuildplanRepository->save($newPlan);

        //copying buildplan modules
        foreach ($buildplan->getModules() as $buildplanModule) {
            $mod = $this->buildplanModuleRepository->prototype();
            $mod->setModuleType($buildplanModule->getModule()->getType());
            $mod->setBuildplan($newPlan);
            $mod->setModule($buildplanModule->getModule());
            $mod->setModuleSpecial(ModuleSpecialAbilityEnum::getHash($buildplanModule->getModule()->getSpecials()));
            $this->buildplanModuleRepository->save($mod);
        }
    }
}