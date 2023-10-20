<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TholianWeb;

use request;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\Interaction\TholianWebUtilInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

final class UnsupportTholianWeb implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_UNSUPPORT_WEB';

    private ShipLoaderInterface $shipLoader;

    private TholianWebUtilInterface $tholianWebUtil;

    private TholianWebRepositoryInterface $tholianWebRepository;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        TholianWebUtilInterface $tholianWebUtil,
        TholianWebRepositoryInterface $tholianWebRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->tholianWebUtil = $tholianWebUtil;
        $this->tholianWebRepository = $tholianWebRepository;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();
        $shipId = request::indInt('id');

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $shipId,
            $userId
        );

        $emitter = $wrapper->getWebEmitterSystemData();

        $this->loggerUtil->log('1');
        if ($emitter === null || $emitter->webUnderConstructionId === null) {
            $this->loggerUtil->log('2');
            throw new SanityCheckException('emitter = null or not constructing', self::ACTION_IDENTIFIER);
        }
        $this->loggerUtil->log('3');

        $ship = $wrapper->get();
        //check if system healthy
        if (!$ship->isWebEmitterHealthy()) {
            throw new SanityCheckException('emitter not healthy', self::ACTION_IDENTIFIER);
        }
        if ($ship->getState() !== ShipStateEnum::SHIP_STATE_WEB_SPINNING) {
            throw new SanityCheckException('ship state is not web spinning', self::ACTION_IDENTIFIER);
        }

        $web = $this->tholianWebRepository->getWebAtLocation($ship);
        if ($web === null || $web->isFinished()) {
            throw new SanityCheckException('no web at location or already finished', self::ACTION_IDENTIFIER);
        }

        $this->tholianWebUtil->releaseWebHelper($wrapper);

        $game->addInformation("Die Unterstützung des Energienetz wurde abgebrochen");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
