<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TholianWeb;

use Override;
use request;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

final class UnsupportTholianWeb implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_UNSUPPORT_WEB';

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private TholianWebUtilInterface $tholianWebUtil,
        private TholianWebRepositoryInterface $tholianWebRepository,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

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
        if ($ship->getState() !== SpacecraftStateEnum::SHIP_STATE_WEB_SPINNING) {
            throw new SanityCheckException('ship state is not web spinning', self::ACTION_IDENTIFIER);
        }

        $web = $this->tholianWebRepository->getWebAtLocation($ship);
        if ($web === null || $web->isFinished()) {
            throw new SanityCheckException('no web at location or already finished', self::ACTION_IDENTIFIER);
        }

        $this->tholianWebUtil->releaseWebHelper($wrapper);

        $game->addInformation("Die Unterstützung des Energienetz wurde abgebrochen");
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
