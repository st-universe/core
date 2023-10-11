<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SalvageCrew;

use request;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionChecker;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;

final class SalvageCrew implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SALVAGE_CREW';

    private ShipLoaderInterface $shipLoader;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private TroopTransferUtilityInterface $troopTransferUtility;

    private ActivatorDeactivatorHelperInterface $helper;

    private CancelRepairInterface $cancelRepair;

    private ShipCrewCalculatorInterface $shipCrewCalculator;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipCrewRepositoryInterface $shipCrewRepository,
        TroopTransferUtilityInterface  $troopTransferUtility,
        ActivatorDeactivatorHelperInterface $helper,
        CancelRepairInterface $cancelRepair,
        ShipCrewCalculatorInterface $shipCrewCalculator,
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->troopTransferUtility = $troopTransferUtility;
        $this->helper = $helper;
        $this->cancelRepair = $cancelRepair;
        $this->shipCrewCalculator = $shipCrewCalculator;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);
        $user = $game->getUser();
        $userId = $user->getId();

        $shipId = request::indInt('id');
        $targetId = request::postIntFatal('target');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $wrappers->getSource();
        $ship = $wrapper->get();
        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        $tradepost = $target->getTradePost();
        if ($tradepost === null) {
            throw new SanityCheckException('target is not a tradepost', self::ACTION_IDENTIFIER);
        }
        if (!InteractionChecker::canInteractWith($ship, $target, $game)) {
            throw new SanityCheckException('can not interact with target', self::ACTION_IDENTIFIER);
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($tradepost->getCrewCountOfUser($user) === 0) {
            throw new SanityCheckException('no crew to rescue', self::ACTION_IDENTIFIER);
        }
        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() < 1) {
            $game->addInformation(sprintf(_('Zum Bergen der Crew wird %d Energie benötigt'), 1));
            return;
        }
        if ($this->cancelRepair->cancelRepair($ship)) {
            $game->addInformation("Die Reparatur wurde abgebrochen");
        }

        $crewToTransfer = min(
            $this->troopTransferUtility->getFreeQuarters($ship),
            $tradepost->getCrewCountOfUser($user)
        );

        if (
            $ship->getCrewCount() + $crewToTransfer > $this->shipCrewCalculator->getMaxCrewCountByRump($ship->getRump())
            && $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)->getMode() == ShipSystemModeEnum::MODE_OFF && !$this->helper->activate($wrapper, ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS, $game)
        ) {
            return;
        }

        $game->addInformation(sprintf('Es wurden %d Crewman geborgen', $crewToTransfer));

        foreach ($tradepost->getCrewAssignments() as $crewAssignment) {
            if ($crewToTransfer === 0) {
                break;
            }
            if ($crewAssignment->getUser() !== $game->getUser()) {
                continue;
            }
            $crewAssignment->setTradepost(null);
            $crewAssignment->setShip($ship);
            $ship->getCrewAssignments()->add($crewAssignment);
            $this->shipCrewRepository->save($crewAssignment);

            $crewToTransfer--;
        }

        $epsSystem->lowerEps(1)->update();

        $this->shipLoader->save($ship);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
