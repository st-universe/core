<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\CancelTholianWeb;

use Doctrine\ORM\EntityManagerInterface;
use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;

final class CancelTholianWeb implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CANCEL_WEB';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private ActivatorDeactivatorHelperInterface $helper;

    private ShipRemoverInterface $shipRemover;

    private LoggerUtilInterface $loggerUtil;

    private EntityManagerInterface $entityManager;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ActivatorDeactivatorHelperInterface $helper,
        ShipRemoverInterface $shipRemover,
        LoggerUtilFactoryInterface $loggerUtilFactory,
        EntityManagerInterface $entityManager
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->helper = $helper;
        $this->shipRemover = $shipRemover;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
        $this->entityManager = $entityManager;
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

        if ($emitter === null || $emitter->ownedWebId === null) {
            return;
        }

        //TODO check if system healthy?

        // activate system
        if (!$this->helper->deactivate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB, $game)) {
            return;
        }

        $web = $emitter->getOwnedTholianWeb();

        //unlink targets
        foreach ($web->getCapturedShips() as $target) {
            $this->loggerUtil->log(sprintf('%s: unlink', $target->getName()));
            $target->setHoldingWeb(null);
            $this->shipRepository->save($target);
        }
        $web->getCapturedShips()->clear();

        $this->entityManager->flush();

        //delete web ship
        $this->shipRemover->remove($web->getWebShip());

        if ($emitter->ownedWebId === $emitter->webUnderConstructionId) {
            $emitter->setWebUnderConstructionId(null);
        }
        $emitter->setOwnedWebId(null)->update();

        $game->addInformation("Der Aufbau des Energienetz wurde abgebrochen");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
