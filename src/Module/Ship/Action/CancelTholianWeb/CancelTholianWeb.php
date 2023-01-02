<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\CancelTholianWeb;

use request;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;

final class CancelTholianWeb implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CANCEL_WEB';

    private ShipLoaderInterface $shipLoader;

    private TholianWebUtilInterface $tholianWebUtil;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        TholianWebUtilInterface $tholianWebUtil,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->tholianWebUtil = $tholianWebUtil;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();
        $shipId = request::indInt('id');

        if ($userId === 126) {
            $this->loggerUtil->init('WEB', LoggerEnum::LEVEL_WARNING);
        }

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $shipId,
            $userId
        );

        $emitter = $wrapper->getWebEmitterSystemData();

        $this->loggerUtil->log('1');
        if ($emitter === null || $emitter->ownedWebId === null) {
            $this->loggerUtil->log('2');
            throw new SanityCheckException('emitter = null or no owned web');
        }
        $this->loggerUtil->log('3');

        $ship = $wrapper->get();
        //check if system healthy
        if (!$ship->isWebEmitterHealthy()) {
            throw new SanityCheckException('emitter not healthy');
        }

        $this->loggerUtil->log('5');

        $web = $emitter->getOwnedTholianWeb();

        $this->loggerUtil->log(sprintf('capturedSize: %d', count($web->getCapturedShips())));
        $this->loggerUtil->log('6');
        //unlink targets
        $this->tholianWebUtil->releaseAllShips($web, $wrapper->getShipWrapperFactory());
        $this->loggerUtil->log('7');

        //delete web ship
        $this->tholianWebUtil->removeWeb($web);
        $this->loggerUtil->log('10');

        if ($emitter->ownedWebId === $emitter->webUnderConstructionId) {
            $emitter->setWebUnderConstructionId(null);
        }
        $emitter->setOwnedWebId(null)->update();

        //reset other web helper
        $this->tholianWebUtil->resetWebHelpers($web, $wrapper->getShipWrapperFactory());

        $ship->setState(ShipStateEnum::SHIP_STATE_NONE);
        $this->shipLoader->save($ship);

        $game->addInformation("Der Aufbau des Energienetz wurde abgebrochen");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
