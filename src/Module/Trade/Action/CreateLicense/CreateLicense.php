<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\CreateLicense;

use Override;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\View\ShowAccounts\ShowAccounts;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeLicenseInfoRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class CreateLicense implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_LICENSE';

    public function __construct(private CreateLicenseRequestInterface $createLicenseRequest, private TradeLicenseInfoRepositoryInterface $TradeLicenseInfoRepository, private TradePostRepositoryInterface $tradePostRepository, private CommodityRepositoryInterface $commodityRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        //$this->loggerUtil->init('trade', LogLevelEnum::ERROR);

        $game->setView(ShowAccounts::VIEW_IDENTIFIER);

        $user = $game->getUser();

        $posts_id = $this->createLicenseRequest->getTradePostId();

        $tradepost = $this->tradePostRepository->find($posts_id);
        if ($tradepost === null) {
            throw new AccessViolationException(sprintf("Tradepost with ID %d not existent! Fool: %d", $posts_id, $user->getId()));
        }
        $tradepost_user = $tradepost->getStation()->getUser();
        if ($tradepost_user !== $user) {
            throw new AccessViolationException(sprintf("Tradepost belongs to other user! Fool: %d", $user->getId()));
        }

        $commodityId = $this->createLicenseRequest->getWantedLicensecommodityId();
        $giveAmount = $this->createLicenseRequest->getWantedLicenseAmount();
        $days = $this->createLicenseRequest->getLicenseDays();

        if ($days < 1 || $days > 365) {
            $game->getInfo()->addInformation("Die Lizenzdauer muss zwischen 1 und 365 Tagen liegen");
            return;
        }

        if ($giveAmount < 1 || $commodityId < 1) {
            $game->getInfo()->addInformation("Es wurde keine Ware oder keine Menge ausgewählt");
            return;
        }

        $commodity = $this->commodityRepository->find($commodityId);
        if ($commodity === null) {
            return;
        }

        $setLicense = $this->TradeLicenseInfoRepository->prototype();
        $setLicense->setTradepost($tradepost);
        $setLicense->setDate(time());
        $setLicense->setCommodity($commodity);
        $setLicense->setAmount($giveAmount);
        $setLicense->setDays($days);

        $this->TradeLicenseInfoRepository->save($setLicense);


        $game->getInfo()->addInformation('Handelslizenz geändert');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
