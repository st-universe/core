<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsTakeOffer;

use Override;
use Stu\Component\Trade\TradeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Component\Spacecraft\ModuleSpecialAbilityEnum;
use Stu\Module\Trade\Lib\TradeLibFactoryInterface;
use Stu\Module\Trade\View\ShowDeals\ShowDeals;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\TradePost;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BuildplanModuleRepositoryInterface;
use Stu\Orm\Repository\DealsRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class DealsTakeOffer implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DEALS_TAKE_OFFER';

    public function __construct(private DealsTakeOfferRequestInterface $dealstakeOfferRequest, private TradeLibFactoryInterface $tradeLibFactory, private DealsRepositoryInterface $dealsRepository, private TradePostRepositoryInterface $tradepostRepository, private TradeLicenseRepositoryInterface $tradeLicenseRepository, private StorageRepositoryInterface $storageRepository, private BuildplanModuleRepositoryInterface $buildplanModuleRepository, private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository, private ShipCreatorInterface $shipCreator, private CreatePrestigeLogInterface $createPrestigeLog) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $user = $game->getUser();
        $dealId = $this->dealstakeOfferRequest->getDealId();
        $amount = $this->dealstakeOfferRequest->getAmount();
        $game->setView(ShowDeals::VIEW_IDENTIFIER);

        $selectedDeal = $this->dealsRepository->find($dealId);

        if ($userId < 100) {
            $game->addInformation(_('NPCs können dieses Angebot nicht annehmen'));
            return;
        }

        if ($amount < 1 && $selectedDeal->getgiveCommodityId() !== null) {
            $game->addInformation(_('Zu geringe Anzahl ausgewählt'));
            return;
        }

        if ($selectedDeal === null) {
            $game->addInformation(_('Das Angebot ist nicht mehr verfügbar'));
            return;
        }

        if (!$this->tradeLicenseRepository->hasFergLicense($userId)) {
            throw new AccessViolationException(sprintf(
                _('UserId %d does not have license for Deals'),
                $userId
            ));
        }

        if ($selectedDeal->getWantPrestige() !== null) {
            $userprestige = $game->getUser()->getPrestige();
            if ($userprestige < $selectedDeal->getWantPrestige()) {
                $game->addInformation(_('Du hast nicht genügend Prestige'));
                return;
            }
        }

        if ($selectedDeal->getwantCommodityId() !== null || $selectedDeal->getWantPrestige() !== null) {
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

            $storageManagerUser = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $user);

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

                if ($selectedDeal->getWantPrestige() !== null) {
                    $userprestige = $game->getUser()->getPrestige();
                    if (
                        $freeStorage <= 0
                    ) {
                        $game->addInformation(_('Dein Warenkonto auf diesem Handelsposten ist voll'));
                        return;
                    }
                    if ($amount * $selectedDeal->getWantPrestige() > $userprestige) {
                        $amount = (int) floor($userprestige / $selectedDeal->getWantPrestige());
                    }
                    if ($amount * $selectedDeal->getgiveCommodityAmount() - $amount * $selectedDeal->getWantPrestige() > $freeStorage) {
                        $amount = (int) floor($freeStorage / ($selectedDeal->getgiveCommodityAmount() - $selectedDeal->getWantPrestige()));
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


            if ($selectedDeal->getAmount() !== null) {
                if ($selectedDeal->getAmount() <= $amount) {
                    $amount = $selectedDeal->getAmount();

                    $this->dealsRepository->delete($selectedDeal);
                } else {
                    //modify deal
                    $selectedDeal->setAmount($selectedDeal->getAmount() - $amount);
                    $this->dealsRepository->save($selectedDeal);
                }
            }

            if ($selectedDeal->getgiveCommodityId() !== null) {
                $storageManagerUser->upperStorage(
                    $selectedDeal->getgiveCommodityId(),
                    (int) $selectedDeal->getgiveCommodityAmount() * $amount
                );
            }

            if ($selectedDeal->getShip() == true) {
                $this->createShip($selectedDeal->getBuildplan(), $tradePost, $userId);
            }

            if ($selectedDeal->getShip() == false && $selectedDeal->getBuildplanId() !== null) {
                $this->copyBuildplan($selectedDeal->getBuildplan(), $user);
            }

            if ($selectedDeal->getwantCommodityId() !== null) {
                $storageManagerUser->lowerStorage(
                    $selectedDeal->getwantCommodityId(),
                    (int) $selectedDeal->getwantCommodityAmount() * $amount
                );
            }

            if ($selectedDeal->getWantPrestige() !== null) {
                $description = sprintf(
                    '-%d Prestige: Eingebüßt beim Deal des Großen Nagus',
                    $amount * $selectedDeal->getWantPrestige()
                );
                $this->createPrestigeLog->createLog(- ($amount * $selectedDeal->getWantPrestige()), $description, $game->getUser(), time());
            }
            $game->addInformation(sprintf(_('Der Deal wurde %d mal angenommen'), $amount));
        }
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

    #[Override]
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
