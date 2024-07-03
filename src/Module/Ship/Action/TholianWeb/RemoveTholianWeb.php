<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TholianWeb;

use Override;
use request;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\Interaction\TholianWebUtilInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class RemoveTholianWeb implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_REMOVE_WEB';

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private TholianWebUtilInterface $tholianWebUtil,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
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
        if ($emitter === null || $emitter->ownedWebId === null) {
            $this->loggerUtil->log('2');
            throw new SanityCheckException('emitter = null or no owned web', self::ACTION_IDENTIFIER);
        }

        $web = $emitter->getOwnedTholianWeb();
        if ($web === null) {
            $this->loggerUtil->log('2');
            throw new SanityCheckException('no own web', self::ACTION_IDENTIFIER);
        }

        if (!$web->isFinished()) {
            $this->loggerUtil->log('2');
            throw new SanityCheckException('web not finished', self::ACTION_IDENTIFIER);
        }
        $this->loggerUtil->log('3');

        $ship = $wrapper->get();
        //check if system healthy
        if (!$ship->isWebEmitterHealthy()) {
            throw new SanityCheckException('emitter not healthy', self::ACTION_IDENTIFIER);
        }

        $this->loggerUtil->log('5');


        $this->loggerUtil->log(sprintf('capturedSize: %d', count($web->getCapturedShips())));
        $this->loggerUtil->log('6');

        //unlink targets
        $this->tholianWebUtil->releaseAllShips($web, $wrapper->getShipWrapperFactory());

        $game->addInformation("Das Energienetz wurde aufgelöst");

        $this->loggerUtil->log('10');

        $emitter->setOwnedWebId(null)->update();
    }


    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
