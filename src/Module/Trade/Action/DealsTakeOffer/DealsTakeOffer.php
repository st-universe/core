<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsTakeOffer;

use Doctrine\ORM\EntityManagerInterface;
use Stu\Exception\AccessViolation;
use Stu\Component\Trade\TradeEnum;
use Stu\Module\ShipModule\ModuleSpecialAbilityEnum;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowDeals\ShowDeals;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;
use Stu\Orm\Repository\TradeTransactionRepositoryInterface;

final class DealsTakeOffer implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEALS_TAKE_OFFER';

    private DealsTakeOfferRequestInterface $dealstakeOfferRequest;

    private TradeLibFactoryInterface $tradeLibFactory;

    private DealsRepositoryInterface $dealsRepository;

    private BuildplanModuleRepositoryInterface $buildplanModuleRepository;

    private TradePostRepositoryInterface $tradepostRepository;

    private StorageRepositoryInterface $storageRepository;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private ShipCreatorInterface $shipCreator;

    private ShipRepositoryInterface $shipRepository;

    private EntityManagerInterface $entityManager;

    private CreatePrestigeLogInterface $createPrestigeLog;

    public function __construct(
        DealsTakeOfferRequestInterface $dealstakeOfferRequest,
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
        CreatePrestigeLogInterface $createPrestigeLog,
        EntityManagerInterface $entityManager
    ) {
        $this->dealstakeOfferRequest = $dealstakeOfferRequest;
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
        $this->entityManager = $entityManager;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $user = $game->getUser();
        $dealId = $this->dealstakeOfferRequest->getDealId();
        $amount = $this->dealstakeOfferRequest->getAmount();
        $game->setView(ShowDeals::VIEW_IDENTIFIER);

        $selectedDeal = $this->dealsRepository->find($dealId);

        if ($amount < 1 && $selectedDeal->getgiveCommodityId() !== null) {
            $game->addInformation(_('Zu geringe Anzahl ausgewählt'));
            return;
        }

        if ($selectedDeal === null) {
            $game->addInformation(_('Das Angebot ist nicht mehr verfügbar'));
            return;
        }

        if (!$this->dealsRepository->getFergLicense($userId)) {
            throw new AccessViolation(sprintf(
                _('UserId %d does not have license for Deals'),
                $userId
            ));
        }

        if ($selectedDeal->getwantCommodityId() !== null || $selectedDeal->getwantPrestige() !== null) {

            if ($selectedDeal->getwantCommodityId() !== null) {
                $storage = $this->storageRepository->getByTradepostAndUserAndCommodity(
                    TradeEnum::DEALS_FERG_TRADEPOST_ID,
                    $userId,
                    $selectedDeal->getWantCommodityId()
                );


                if ($storage === null || $storage->getAmount() < $selectedDeal->getwantCommodityAmount()) {
                    $game->addInformation(sprintf(
                        _('Es befindet sich nicht genügend %s auf diesem Handelsposten'),
                        $selectedDeal->getWantedCommodity()->getName()
                    ));
                    return;
                }
            }

            $tradePost = $this->tradepostRepository->getFergTradePost(TradeEnum::DEALS_FERG_TRADEPOST_ID);

            $storageManagerUser = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $userId);

            $freeStorage = $storageManagerUser->getFreeStorage();

            if ($selectedDeal->getgiveCommodityId() !== null) {
                if ($selectedDeal->getwantCommodityId() !== null) {

                    if (
                        $freeStorage <= 0 &&
                        $selectedDeal->getgiveCommodityAmount() > $selectedDeal->getwantCommodityAmount()
                    ) {
                        $game->addInformation(_('Dein Warenkonto auf diesem Handelsposten ist voll'));
                        return;
                    }
                    if ($amount * $selectedDeal->getwantCommodityAmount() > $storage->getAmount()) {
                        $amount = (int) floor($storage->getAmount() / $selectedDeal->getwantCommodityAmount());
                    }
                    if ($amount * $selectedDeal->getgiveCommodityAmount() - $amount * $selectedDeal->getwantCommodityAmount() > $freeStorage) {
                        $amount = (int) floor($freeStorage / ($selectedDeal->getgiveCommodityAmount() - $selectedDeal->getwantCommodityAmount()));
                        if ($amount <= 0) {
                            $game->addInformation(_('Es steht für diese Transaktion nicht genügend Platz in deinem Warenkonto zur Verfügung'));
                            return;
                        }
                    }
                }

                if ($selectedDeal->getwantPrestige() !== null) {
                    $userprestige = $game->getUser()->getPrestige();
                    if (
                        $freeStorage <= 0
                    ) {
                        $game->addInformation(_('Dein Warenkonto auf diesem Handelsposten ist voll'));
                        return;
                    }
                    if ($amount * $selectedDeal->getwantPrestige() > $userprestige) {
                        $amount = (int) floor($userprestige / $selectedDeal->getwantPrestige());
                    }
                    if ($amount * $selectedDeal->getgiveCommodityAmount() - $amount * $selectedDeal->getwantPrestige() > $freeStorage) {
                        $amount = (int) floor($freeStorage / ($selectedDeal->getgiveCommodityAmount() - $selectedDeal->getwantPrestige()));
                        if ($amount <= 0) {
                            $game->addInformation(_('Es steht für diese Transaktion nicht genügend Platz in deinem Warenkonto zur Verfügung'));
                            return;
                        }
                    }
                }
            }

            if ($selectedDeal->getBuildplanId() !== null) {
                $amount = 1;
            }

            if ($selectedDeal->getAmount() <= $amount) {
                $amount = $selectedDeal->getAmount();

                $this->dealsRepository->delete($selectedDeal);
            } else {

                //modify deal
                $selectedDeal->setAmount($selectedDeal->getAmount() - (int) $amount);
                $this->dealsRepository->save($selectedDeal);
            }


            if ($selectedDeal->getgiveCommodityId() !== null) {
                $storageManagerUser->upperStorage(
                    (int) $selectedDeal->getgiveCommodityId(),
                    (int) $selectedDeal->getgiveCommodityAmount() * $amount
                );
            }

            if ($selectedDeal->getShip() == true) {

                $this->createShip($selectedDeal->getBuildplan(), $tradePost, $userId);
            }

            if ($selectedDeal->getShip() == false && $selectedDeal->getBuildplanId() !== null) {
                $this->createBuildplan($selectedDeal, $user);
            }

            if ($selectedDeal->getwantCommodityId() !== null) {
                $storageManagerUser->lowerStorage(
                    (int) $selectedDeal->getwantCommodityId(),
                    (int) $selectedDeal->getwantCommodityAmount() * $amount
                );
            }

            if ($selectedDeal->getwantPrestige() !== null) {
                $description = sprintf(
                    '-%d Prestige: Eingebüßt beim Deal der Großen Nagus',
                    $amount * $selectedDeal->getwantPrestige()
                );
                $this->createPrestigeLog->createLog(- ($amount * $selectedDeal->getwantPrestige()), $description, $game->getUser(), time());
            }
            $game->addInformation(sprintf(_('Der Deal wurde %d mal angenommen'), $amount));
        }
    }


    private function createShip($buildplan, $tradePost, $userId): void
    {

        $ship = $this->shipCreator->createBy($userId, $buildplan->getRump()->getId(), $buildplan->getId());

        $this->entityManager->flush();

        $ship->setEps((int)floor($ship->getTheoreticalMaxEps() / 4));
        $ship->setReactorLoad((int)floor($ship->getReactorCapacity() / 4));
        $ship->setEBatt(0);
        $ship->updateLocation($tradePost->getShip()->getMap(), $tradePost->getShip()->getStarsystemMap());


        $this->shipRepository->save($ship);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }

    private function createBuildplan($selectedDeal, $user): void
    {
        $plan = $this->shipBuildplanRepository->prototype();
        $plan->setUser($user);
        $plan->setRump($selectedDeal->getBuildplan()->getRump());
        $plan->setName($selectedDeal->getBuildplan()->getName());
        $plan->setSignature($selectedDeal->getBuildplan()->getSignature());
        $plan->setBuildtime($selectedDeal->getBuildplan()->getBuildtime());
        $plan->setCrew($selectedDeal->getBuildplan()->getCrew());

        $this->shipBuildplanRepository->save($plan);
        $this->entityManager->flush();


        $modules = $selectedDeal->getBuildplan()->getModules();

        foreach ($modules as $obj) {
            $mod = $this->buildplanModuleRepository->prototype();
            $mod->setModuleType((int) $obj->getModule()->getType());
            $mod->setBuildplan($plan);
            $mod->setModule($obj);
            $mod->setModuleSpecial(ModuleSpecialAbilityEnum::getHash($obj->getSpecials()));
        }

        $this->buildplanModuleRepository->save($mod);
    }
}