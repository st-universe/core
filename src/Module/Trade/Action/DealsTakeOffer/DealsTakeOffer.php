<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\DealsTakeOffer;

use RuntimeException;
use Stu\Component\Spacecraft\ModuleSpecialAbilityEnum;
use Stu\Component\Trade\TradeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
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

    public function __construct(
        private DealsTakeOfferRequestInterface $dealstakeOfferRequest,
        private TradeLibFactoryInterface $tradeLibFactory,
        private DealsRepositoryInterface $dealsRepository,
        private TradePostRepositoryInterface $tradepostRepository,
        private TradeLicenseRepositoryInterface $tradeLicenseRepository,
        private StorageRepositoryInterface $storageRepository,
        private BuildplanModuleRepositoryInterface $buildplanModuleRepository,
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private ShipCreatorInterface $shipCreator,
        private CreatePrestigeLogInterface $createPrestigeLog
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        $user = $game->getUser();
        $dealId = $this->dealstakeOfferRequest->getDealId();
        $amount = $this->dealstakeOfferRequest->getAmount();
        $game->setView(ShowDeals::VIEW_IDENTIFIER);

        $selectedDeal = $this->dealsRepository->find($dealId);
        if ($selectedDeal === null) {
            $game->getInfo()->addInformation(_('Das Angebot ist nicht mehr verfügbar'));
            return;
        }

        if ($userId < 100) {
            $game->getInfo()->addInformation(_('NPCs können dieses Angebot nicht annehmen'));
            return;
        }

        if ($amount < 1 && $selectedDeal->getGiveCommodityId() !== null) {
            $game->getInfo()->addInformation(_('Zu geringe Anzahl ausgewählt'));
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
                $game->getInfo()->addInformation(_('Du hast nicht genügend Prestige'));
                return;
            }
        }

        $wantedCommodity = $selectedDeal->getWantedCommodity();
        if ($wantedCommodity !== null || $selectedDeal->getWantPrestige() !== null) {
            if ($wantedCommodity !== null) {
                $storage = $this->storageRepository->getByTradepostAndUserAndCommodity(
                    TradeEnum::DEALS_FERG_TRADEPOST_ID,
                    $userId,
                    $wantedCommodity->getId()
                );

                if ($storage === null || $storage->getAmount() < $selectedDeal->getWantCommodityAmount()) {
                    $game->getInfo()->addInformation(sprintf(
                        _('Es befindet sich nicht genügend %s auf diesem Handelsposten'),
                        $wantedCommodity->getName()
                    ));
                    return;
                }
            }

            $tradePost = $this->tradepostRepository->find(TradeEnum::DEALS_FERG_TRADEPOST_ID);
            if ($tradePost === null) {
                throw new RuntimeException('no deals ferg tradepost found');
            }

            $storageManagerUser = $this->tradeLibFactory->createTradePostStorageManager($tradePost, $user);

            $freeStorage = $storageManagerUser->getFreeStorage();

            if ($selectedDeal->getGiveCommodityId() !== null) {
                if ($wantedCommodity !== null) {

                    $storage = $this->storageRepository->getByTradepostAndUserAndCommodity(
                        TradeEnum::DEALS_FERG_TRADEPOST_ID,
                        $userId,
                        $wantedCommodity->getId()
                    );
                    $storageAmount = $storage?->getAmount() ?? 0;

                    if (
                        $freeStorage <= 0 &&
                        $selectedDeal->getGiveCommodityAmount() > $selectedDeal->getWantCommodityAmount()
                    ) {
                        $game->getInfo()->addInformation(_('Dein Warenkonto auf diesem Handelsposten ist voll'));
                        return;
                    }
                    if ($amount * $selectedDeal->getWantCommodityAmount() > $storageAmount) {
                        $amount = (int) floor($storageAmount / $selectedDeal->getWantCommodityAmount());
                    }
                    if ($amount * $selectedDeal->getGiveCommodityAmount() - $amount * $selectedDeal->getWantCommodityAmount() > $freeStorage) {
                        $amount = (int) floor($freeStorage / ($selectedDeal->getGiveCommodityAmount() - $selectedDeal->getWantCommodityAmount()));
                        if ($amount <= 0) {
                            $game->getInfo()->addInformation(_('Es steht für diese Transaktion nicht genügend Platz in deinem Warenkonto zur Verfügung'));
                            return;
                        }
                    }
                }

                if ($selectedDeal->getWantPrestige() !== null) {
                    $userprestige = $game->getUser()->getPrestige();
                    if (
                        $freeStorage <= 0
                    ) {
                        $game->getInfo()->addInformation(_('Dein Warenkonto auf diesem Handelsposten ist voll'));
                        return;
                    }
                    if ($amount * $selectedDeal->getWantPrestige() > $userprestige) {
                        $amount = (int) floor($userprestige / $selectedDeal->getWantPrestige());
                    }
                    if ($amount * $selectedDeal->getGiveCommodityAmount() - $amount * $selectedDeal->getWantPrestige() > $freeStorage) {
                        $amount = (int) floor($freeStorage / ($selectedDeal->getGiveCommodityAmount() - $selectedDeal->getWantPrestige()));
                        if ($amount <= 0) {
                            $game->getInfo()->addInformation(_('Es steht für diese Transaktion nicht genügend Platz in deinem Warenkonto zur Verfügung'));
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

            $givenCommodityId = $selectedDeal->getGiveCommodityId();
            if ($givenCommodityId !== null) {
                $storageManagerUser->upperStorage(
                    $givenCommodityId,
                    (int) $selectedDeal->getGiveCommodityAmount() * $amount
                );
            }

            $buildplan = $selectedDeal->getBuildplan();
            if ($buildplan !== null) {
                if ($selectedDeal->getShip() === true) {
                    $this->createShip($buildplan, $tradePost, $userId);
                } else {
                    $this->copyBuildplan($buildplan, $user);
                }
            }

            if ($selectedDeal->getWantCommodityId() !== null) {
                $storageManagerUser->lowerStorage(
                    $selectedDeal->getWantCommodityId(),
                    (int) $selectedDeal->getWantCommodityAmount() * $amount
                );
            }

            if ($selectedDeal->getWantPrestige() !== null) {
                $description = sprintf(
                    '-%d Prestige: Eingebüßt beim Deal des Großen Nagus',
                    $amount * $selectedDeal->getWantPrestige()
                );
                $this->createPrestigeLog->createLog(- ($amount * $selectedDeal->getWantPrestige()), $description, $game->getUser(), time());
            }
            $game->getInfo()->addInformationf('Der Deal wurde %d mal angenommen', $amount);
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
