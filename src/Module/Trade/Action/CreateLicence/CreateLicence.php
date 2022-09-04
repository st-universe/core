<?php

declare(strict_types=1);

namespace Stu\Module\Trade\Action\CreateLicence;

use Stu\Exception\AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Trade\View\ShowAccounts\ShowAccounts;
use Stu\Orm\Repository\TradeCreateLicenceRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class CreateLicence implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CREATE_LICENCE';

    private CreateLicenceRequestInterface $createLicenceRequest;

    private TradeCreateLicenceRepositoryInterface $tradeCreateLicenceRepository;

    private TradePostRepositoryInterface $tradePostRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        CreateLicenceRequestInterface $createLicenceRequest,
        TradeCreateLicenceRepositoryInterface $tradeCreateLicenceRepository,
        TradePostRepositoryInterface $tradePostRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->createLicenceRequest = $createLicenceRequest;
        $this->tradeCreateLicenceRepository = $tradeCreateLicenceRepository;
        $this->tradePostRepository = $tradePostRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        if ($game->getUser()->getId() === 11) {
            $this->loggerUtil->init('trade', LoggerEnum::LEVEL_ERROR);
        }

        $game->setView(ShowAccounts::VIEW_IDENTIFIER);

        $user = $game->getUser();

        $posts_id = $this->createLicenceRequest->getTradePostId();

        $this->loggerUtil->log(sprintf('posts_id: %d', $posts_id));

        $tradepost = $this->tradePostRepository->find($posts_id);
        if ($tradepost === null) {
            throw new AccessViolation(sprintf("Tradepost with ID %d not existent! Fool: %d", $posts_id, $user->getId()));
        }
        $tradepost_user = $tradepost->getShip()->getUser();
        if ($tradepost_user !== $user) {
            throw new AccessViolation(sprintf("Tradepost belongs to other user! Fool: %d", $user->getId()));
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

        $setLicence = $this->tradeCreateLicenceRepository->prototype();
        $setLicence->setTradePostId((int) $posts_id);
        $setLicence->setDate(time());
        $setLicence->setGoodsId((int) $goods_id);
        $setLicence->setAmount((int) $giveAmount);
        $setLicence->setDays($days);

        $this->tradeCreateLicenceRepository->save($setLicence);


        $game->addInformation('Handelslizenz geändert');
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
