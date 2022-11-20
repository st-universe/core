<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SalvageCrew;

use request;
use Stu\Component\Ship\System\Exception\SystemNotActivatableException;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\TroopTransferUtilityInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;

final class SalvageCrew implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SALVAGE_CREW';

    private ShipLoaderInterface $shipLoader;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private TroopTransferUtilityInterface $troopTransferUtility;

    private ActivatorDeactivatorHelperInterface $helper;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipCrewRepositoryInterface $shipCrewRepository,
        TroopTransferUtilityInterface  $troopTransferUtility,
        ActivatorDeactivatorHelperInterface $helper,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->troopTransferUtility = $troopTransferUtility;
        $this->helper = $helper;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);
        $userId = $game->getUser()->getId();

        $shipId = request::indInt('id');
        $targetId = request::postIntFatal('target');

        $shipArray = $this->shipLoader->getByIdAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $ship = $shipArray[$shipId];
        $target = $shipArray[$targetId];
        if ($target === null) {
            return;
        }
        $tradepost = $target->getTradePost();
        if ($tradepost === null) {
            throw new SanityCheckException('target is not a tradepost');
        }
        if (!$ship->canInteractWith($target)) {
            throw new SanityCheckException('can not interact with target');
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($tradepost->getCrewCountOfCurrentUser() === 0) {
            throw new SanityCheckException('no crew to rescue');
        }
        if ($ship->getEps() < 1) {
            $game->addInformation(sprintf(_('Zum Bergen der Crew wird %d Energie benötigt'), 1));
            return;
        }
        if ($ship->cancelRepair()) {
            $game->addInformation("Die Reparatur wurde abgebrochen");
        }

        $crewToTransfer = min($this->troopTransferUtility->getFreeQuarters($ship), $tradepost->getCrewCountOfCurrentUser());

        $game->addInformation(sprintf('Es wurden %d Crewman geborgen', $crewToTransfer));

        foreach ($tradepost->getCrewAssignments() as $crewAssignment) {
            if ($crewToTransfer === 0) {
                break;
            }
            if (!$crewAssignment->getUser() === $game->getUser()) {
                continue;
            }
            $crewAssignment->setTradepost(null);
            $crewAssignment->setShip($ship);
            $ship->getCrewlist()->add($crewAssignment);
            $this->shipCrewRepository->save($crewAssignment);

            $crewToTransfer--;
        }

        if ($ship->getCrewCount() > $ship->getRump()->getMaxCrewCount() && $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)->getMode() == ShipSystemModeEnum::MODE_OFF) {
            if (!$this->helper->activate($shipId, ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS, $game)) {
                throw new SystemNotActivatableException();
            }
        }

        $ship->setEps($ship->getEps() - 1);

        $this->shipLoader->save($ship);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
