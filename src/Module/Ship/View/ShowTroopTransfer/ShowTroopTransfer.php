<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowTroopTransfer;

use request;

use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionChecker;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowTroopTransfer implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TROOP_TRANSFER';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private TroopTransferUtilityInterface $transferUtility;

    private ShipCrewCalculatorInterface $shipCrewCalculator;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ColonyRepositoryInterface $colonyRepository,
        ShipCrewCalculatorInterface $shipCrewCalculator,
        TroopTransferUtilityInterface $transferUtility
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->colonyRepository = $colonyRepository;
        $this->transferUtility = $transferUtility;
        $this->shipCrewCalculator = $shipCrewCalculator;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $user->getId()
        );

        //if (!$ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS)) {
        //    return;
        //}

        $isColony = request::has('isColony');
        $isUnload = request::has('isUnload');

        $isUplinkSituation = false;

        if ($isColony) {
            $target = $this->colonyRepository->find(request::getIntFatal('target'));
            if ($target === null) {
                return;
            }

            if ($isUnload) {
                $game->setPageTitle(_('Truppen zur Kolonie beamen'));
                $max = $this->transferUtility->getBeamableTroopCount($ship);
            } else {
                $max = min(
                    $target->getCrewAssignmentAmount(),
                    $this->transferUtility->getFreeQuarters($ship)
                );
                $game->setPageTitle(_('Truppen von Kolonie beamen'));
            }
        } else {
            $target = $this->shipRepository->find(request::getIntFatal('target'));
            if ($target === null) {
                return;
            }

            $ownCrewOnTarget = $this->transferUtility->ownCrewOnTarget($user, $target);

            if ($target->getUser() !== $user) {
                if ($target->hasUplink()) {
                    $isUplinkSituation = true;
                } else {
                    return;
                }
            }

            if ($isUnload) {
                $max = min(
                    $this->transferUtility->getBeamableTroopCount($ship),
                    $this->transferUtility->getFreeQuarters($target),
                    $isUplinkSituation ? ($ownCrewOnTarget === 0 ? 1 : 0) : PHP_INT_MAX
                );
                $game->setPageTitle(_('Truppen zu Schiff beamen'));
            } else {
                $max = min(
                    $ownCrewOnTarget,
                    $this->transferUtility->getFreeQuarters($ship)
                );
                $game->setPageTitle(_('Truppen von Schiff beamen'));
            }

            $game->setTemplateVar(
                'SHIP_MAX_CREW_COUNT',
                $this->shipCrewCalculator->getMaxCrewCountByShip($target)
            );
        }

        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/entity_not_available');

        if (!InteractionChecker::canInteractWith($ship, $target, $game)) {
            return;
        }

        if (!$isUplinkSituation && $target->getUser() !== $ship->getUser()) {
            return;
        }

        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/show_troop_transfer');

        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('target', $target);
        $game->setTemplateVar('MAXIMUM', $max);
        $game->setTemplateVar('IS_UNLOAD', $isUnload);
        $game->setTemplateVar('IS_COLONY', $isColony);
    }
}