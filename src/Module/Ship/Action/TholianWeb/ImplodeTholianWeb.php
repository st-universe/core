<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TholianWeb;

use request;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Battle\Weapon\TholianWebWeaponPhaseInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\Interaction\TholianWebUtilInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class ImplodeTholianWeb implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_IMPLODE_WEB';

    private ShipLoaderInterface $shipLoader;

    private TholianWebUtilInterface $tholianWebUtil;

    private PrivateMessageSenderInterface $privateMessageSender;

    private TholianWebWeaponPhaseInterface $tholianWebWeaponPhase;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        TholianWebUtilInterface $tholianWebUtil,
        PrivateMessageSenderInterface $privateMessageSender,
        TholianWebWeaponPhaseInterface $tholianWebWeaponPhase,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->tholianWebUtil = $tholianWebUtil;
        $this->privateMessageSender = $privateMessageSender;
        $this->tholianWebWeaponPhase = $tholianWebWeaponPhase;
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
        if ($emitter === null) {
            throw new SanityCheckException('emitter = null', self::ACTION_IDENTIFIER);
        }

        $web = $emitter->getOwnedTholianWeb();
        if ($web === null) {
            $this->loggerUtil->log('2');
            throw new SanityCheckException('no owned web', self::ACTION_IDENTIFIER);
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

        $game->addInformation("Das Energienetz ist implodiert");

        //damage captured targets
        foreach ($web->getCapturedShips() as $target) {
            $this->loggerUtil->log(sprintf('capturedTargetId: %d', $target->getId()));
            $targetWrapper = $wrapper->getShipWrapperFactory()->wrapShip($target);
            $this->tholianWebUtil->releaseShipFromWeb($targetWrapper);

            //don't damage trumfields
            if ($target->isDestroyed()) {
                continue;
            }

            //store these values, cause they are changed in case of destruction
            $targetUserId = $target->getUser()->getId();
            $isTargetBase = $target->isBase();

            $informations = $this->tholianWebWeaponPhase->damageCapturedShip($targetWrapper, $game);

            //notify target owner
            $this->privateMessageSender->send(
                $userId,
                $targetUserId,
                $informations->getInformationsAsString(),
                $isTargetBase ? PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
            );

            $game->addInformationWrapper($informations);
        }


        $this->loggerUtil->log('10');

        $emitter->setOwnedWebId(null)->update();
    }



    public function performSessionCheck(): bool
    {
        return true;
    }
}
