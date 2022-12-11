<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsTakeAuction;

use Stu\Exception\AccessViolation;
use Stu\Component\Trade\TradeEnum;
use Stu\Module\ShipModule\ModuleSpecialAbilityEnum;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowDeals\ShowDeals;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Entity\TradePostInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\TradeTransactionRepositoryInterface;

final class DealsTakeAuction implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEALS_TAKE_AUCTION';

    private DealsTakeAuctionRequestInterface $dealstakeAuctionRequest;

    private TradeLibFactoryInterface $tradeLibFactory;

    private DealsRepositoryInterface $dealsRepository;

    private BuildplanModuleRepositoryInterface $buildplanModuleRepository;

    private TradePostRepositoryInterface $tradepostRepository;

    private StorageRepositoryInterface $storageRepository;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private ShipCreatorInterface $shipCreator;

    private ShipRepositoryInterface $shipRepository;

    private CreatePrestigeLogInterface $createPrestigeLog;

    public function __construct(
        DealsTakeAuctionRequestInterface $dealstakeAuctionRequest,
        TradeLibFactoryInterface $tradeLibFactory,
        DealsRepositoryInterface $dealsRepository,
        TradePostRepositoryInterface $tradepostRepository,
        TradeLicenseRepositoryInterface $tradeLicenseRepository,
        TradeTransactionRepositoryInterface $tradeTransactionRepository,
        StorageRepositoryInterface $storageRepository,
        BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ShipCreatorInterface $shipCreator,
        ShipRepositoryInterface $shipRepository,
        CreatePrestigeLogInterface $createPrestigeLog
    ) {
        $this->dealstakeAuctionRequest = $dealstakeAuctionRequest;
        $this->tradeLibFactory = $tradeLibFactory;
        $this->buildplanModuleRepository = $buildplanModuleRepository;
        $this->tradepostRepository = $tradepostRepository;
        $this->dealsRepository = $dealsRepository;
        $this->tradeLicenseRepository = $tradeLicenseRepository;
        $this->tradeTransactionRepository = $tradeTransactionRepository;
        $this->storageRepository = $storageRepository;
        $this->createPrestigeLog = $createPrestigeLog;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->shipRepository = $shipRepository;
        $this->shipCreator = $shipCreator;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $user = $game->getUser();
        $dealId = $this->dealstakeAuctionRequest->getDealId();
        $game->setView(ShowDeals::VIEW_IDENTIFIER);

        $auction = $this->dealsRepository->find($dealId);

        if ($auction->getAuctionUser()->getId() != $userId) {
            return;
        }

        if ($auction === null) {
            $game->addInformation(_('Das Angebot ist nicht mehr verfügbar'));
            return;
        }

        if (!$this->dealsRepository->getFergLicense($userId)) {
            throw new AccessViolation(sprintf(
                _('UserId %d does not have license for Deals'),
                $userId
            ));
        }

        if ($auction->getgiveCommodityId() !== null || $auction->getwantPrestige() !== null) {

            if ($auction->getgiveCommodityId() !== null) {
                $storage = $this->storageRepository->getByTradepostAndUserAndCommodity(
                    TradeEnum::DEALS_FERG_TRADEPOST_ID,
                    $userId,
                    $auction->getWantCommodityId()
                );


                if ($storage === null || $storage->getAmount() < $auction->getgiveCommodityAmount()) {
                    $game->addInformation(sprintf(
                        _('Es befindet sich nicht genügend Platz auf diesem Handelsposten')
                    ));
                    return;
                }
            }

            $tradePost = $this->tradepostRepository->getFergTradePost(TradeEnum::DEALS_FERG_TRADEPOST_ID);

            $storageManagerUser = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $userId);

            $freeStorage = $storageManagerUser->getFreeStorage();

            if ($auction->getAuctionAmount() <  $auction->getHighestBid()->getMaxAmount()) {
                $currentBidAmount = $auction->getAuctionAmount();
                $currentMaxAmount = $auction->getHighestBid()->getMaxAmount();
                if ($auction->getWantedCommodity() != null) {
                    if ($storage->getAmount() < ($currentMaxAmount - $currentBidAmount)) {
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

                if ($auction->getwantPrestige() != null) {

                    $description = sprintf(
                        '%d Prestige: Du hast Prestige bei einer Auktion zurückerhalten, weil die Maximalgebot über dem Höchstgebot lag',
                        $currentMaxAmount - $currentBidAmount
                    );
                    $this->createPrestigeLog->createLog($currentMaxAmount - $currentBidAmount, $description, $user, time());

                    $game->addInformation(sprintf(
                        _('Dir wurden %d Prestige gutgeschrieben'),
                        $currentMaxAmount - $currentBidAmount,
                    ));
                }
            }

            if ($auction->getBuildplanId() !== null) {
                $amount = 1;
            }

            if ($auction->getgiveCommodityId() !== null) {
                $storageManagerUser->upperStorage(
                    (int) $auction->getgiveCommodityId(),
                    (int) $auction->getgiveCommodityAmount() * $amount
                );

                $game->addInformation(sprintf(_('Du hast %d %s erhalten'), (int) $auction->getgiveCommodityAmount() * $amount, $auction->getgiveCommodity()->getName()));
            }

            if ($auction->getShip() == true) {

                $this->createShip($auction->getBuildplan(), $tradePost, $userId);
                $game->addInformation(sprintf(_('Du hast dein Schiff erhalten'), $amount));
            }

            if ($auction->getShip() == false && $auction->getBuildplanId() !== null) {
                $this->copyBuildplan($auction->getBuildplan(), $user);

                $game->addInformation(sprintf(_('Du hast deinen Bauplan erhalten'), $amount));
            }
        }

        $this->dealsRepository->delete($auction);
    }


    private function createShip(ShipBuildplanInterface $buildplan, TradePostInterface $tradePost, int $userId): void
    {
        $ship = $this->shipCreator->createBy($userId, $buildplan->getRump()->getId(), $buildplan->getId());

        $ship->setEps((int)floor($ship->getTheoreticalMaxEps() / 4));
        $ship->setReactorLoad((int)floor($ship->getReactorCapacity() / 4));
        $ship->updateLocation($tradePost->getShip()->getMap(), $tradePost->getShip()->getStarsystemMap());

        $this->shipRepository->save($ship);
    }

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
            $mod->setModuleType((int) $buildplanModule->getModule()->getType());
            $mod->setBuildplan($newPlan);
            $mod->setModule($buildplanModule->getModule());
            $mod->setModuleSpecial(ModuleSpecialAbilityEnum::getHash($buildplanModule->getModule()->getSpecials()));
            $this->buildplanModuleRepository->save($mod);
        }
    }
}