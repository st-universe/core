<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TholianWeb;

use Override;
use request;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

final class SupportTholianWeb implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SUPPORT_WEB';

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private TholianWebUtilInterface $tholianWebUtil,
        private TholianWebRepositoryInterface $tholianWebRepository,
        private SpacecraftSystemRepositoryInterface $shipSystemRepository,
        private SpacecraftStateChangerInterface $spacecraftStateChanger,
        private ActivatorDeactivatorHelperInterface $helper,
        private StuTime $stuTime,
        private PrivateMessageSenderInterface $privateMessageSender,
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
        if ($emitter === null || $emitter->webUnderConstructionId !== null) {
            $this->loggerUtil->log('2');
            throw new SanityCheckException('emitter = null or already constructing', self::ACTION_IDENTIFIER);
        }
        $this->loggerUtil->log('3');

        $ship = $wrapper->get();
        //check if system healthy
        if (!$ship->isWebEmitterHealthy()) {
            throw new SanityCheckException('emitter not healthy', self::ACTION_IDENTIFIER);
        }

        $web = $this->tholianWebRepository->getWebAtLocation($ship);
        if ($web === null || $web->isFinished()) {
            throw new SanityCheckException('no web at location or already finished', self::ACTION_IDENTIFIER);
        }

        if ($ship->isWarped()) {
            $game->addInformation("Aktion nicht möglich, Schiff befindet sich im Warp");
            return;
        }

        // activate system
        if (!$this->helper->activate($wrapper, SpacecraftSystemTypeEnum::THOLIAN_WEB, $game)) {
            return;
        }

        $currentSpinnerSystems = $this->shipSystemRepository->getWebConstructingShipSystems($web->getId());

        $emitter->setWebUnderConstructionId($web->getId())->update();
        $this->spacecraftStateChanger->changeState($wrapper, SpacecraftStateEnum::WEB_SPINNING);

        $finishedTime = $this->tholianWebUtil->updateWebFinishTime($web, 1);
        if ($finishedTime === null) {
            throw new RuntimeException('this should not happen');
        }

        $finishTimeString = $this->stuTime->transformToStuDateTime($finishedTime);

        //notify other web spinners
        foreach ($currentSpinnerSystems as $shipSystem) {
            $this->privateMessageSender->send(
                $userId,
                $shipSystem->getSpacecraft()->getUser()->getId(),
                sprintf(
                    'Die %s unterstützt den Netzaufbau in Sektor %s, Fertigstellung: %s',
                    $ship->getName(),
                    $ship->getSectorString(),
                    $finishTimeString
                ),
                PrivateMessageFolderTypeEnum::SPECIAL_SHIP
            );
        }

        $game->addInformationf(
            "Der Aufbau des Energienetz wird unterstützt, Fertigstellung: %s",
            $finishTimeString
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
