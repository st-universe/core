<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\CreateLicence;

use Stu\Exception\AccessViolation;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Trade\View\ShowAccounts\ShowAccounts;
use Stu\Orm\Entity\CommodityInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\Orm\Repository\TradeCreateLicenceRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class CreateLicence implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CREATE_LICENCE';

    private CreateLicenceRequestInterface $createLicenceRequest;

    private CommodityRepositoryInterface $commodityRepository;

    private TradeCreateLicenceRepositoryInterface $tradeLicenceRepository;

    private TradePostRepositoryInterface $tradePostRepository;

    public function __construct(
        CreateLicenceRequestInterface $createLicenceRequest,
        CommodityRepositoryInterface $commodityRepository,
        TradeCreateLicenceRepositoryInterface $tradeLicenceRepository,
        TradePostRepositoryInterface $tradePostRepository
    ) {
        $this->createLicenceRequest = $createLicenceRequest;
        $this->commodityRepository = $commodityRepository;
        $this->tradeLicenceRepository = $tradeLicenceRepository;
        $this->tradePostRepository = $tradePostRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowAccounts::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $posts_id = $this->createLicenceRequest->getTradePostId();
        $tradepost_user = $this->tradeLicenceRepository->getUserByTradepost($posts_id);
        if ($posts_id === null) {
            throw new AccessViolation(sprintf("Tradepost not existent! Fool: %d", $posts_id));
        }
        if ((int) $tradepost_user !== $userId) {
            throw new AccessViolation(sprintf("Tradepost belongs to other user! Fool: %d", $userId));
        }


        $goods_id = $this->createLicenceRequest->getWantedLicenceGoodId();
        $giveAmount = $this->createLicenceRequest->getWantedLicenceAmount();
        $days = $this->createLicenceRequest->getLicenceDays();


        if ($days < 1 || $days > 365) {
            $game->addInformation("Die Lizenzdauer muss zwischen 1 und 365 Tagen liegen");
            return;
        }

        if ($giveAmount < 1 || $goods_id < 1 || $giveAmount === null || $goods_id === null) {
            $game->addInformation("Es wurde keine Ware oder keine Menge ausgewählt");
            return;
        }

        $setLicence = $this->tradeLicenceRepository->prototype();
        $setLicence->setTradePostId((int) $posts_id);
        $setLicence->setDate(time());
        $setLicence->setGoodsId((int) $goods_id);
        $setLicence->setAmount((int) $giveAmount);
        $setLicence->setDays($days);

        $this->tradeLicenceRepository->save($setLicence);


        $game->addInformation('Handelslizenz geändert');
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}