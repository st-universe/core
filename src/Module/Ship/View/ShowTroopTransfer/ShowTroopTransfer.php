<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowTroopTransfer;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\TroopTransferUtilityInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowTroopTransfer implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_TROOP_TRANSFER';

    private ShipLoaderInterface $shipLoader;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipRepositoryInterface $shipRepository;
    
    private TroopTransferUtilityInterface $transferUtility;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ColonyRepositoryInterface $colonyRepository,
        ShipRepositoryInterface $shipRepository,
        TroopTransferUtilityInterface $transferUtility
    ) {
        $this->shipLoader = $shipLoader;
        $this->colonyRepository = $colonyRepository;
        $this->shipRepository = $shipRepository;
        $this->transferUtility = $transferUtility;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $user->getId()
        );

        if (!$ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS))
        {
            return;
        }

        $isColony = request::getVarByMethod(request::getvars(), 'isColony');
        $isUnload = request::getVarByMethod(request::getvars(), 'isUnload');


        if ($isColony)
        {
            $target = $this->colonyRepository->find((int)request::getIntFatal('target'));
            
            if ($isUnload)
            {
                $game->setPageTitle(_('Truppen zur Kolonie beamen'));
                $max = $this->transferUtility->getBeamableTroopCount($ship);
            }
            else {
                $max = min($ship->getUser()->getFreeCrewCount(),
                            $this->transferUtility->getFreeQuarters($ship));
                $game->setPageTitle(_('Truppen von Kolonie beamen'));
            }
            
        }
        else
        {
            $target = $this->shipRepository->find((int)request::getIntFatal('target'));

            if ($isUnload)
            {
                $max = min($this->transferUtility->getBeamableTroopCount($ship),
                            $this->transferUtility->getFreeQuarters($target));
                $game->setPageTitle(_('Truppen zu Schiff beamen'));
            }
            else {
                $max = min($target->getCrewCount(),
                            $this->transferUtility->getFreeQuarters($ship));
                $game->setPageTitle(_('Truppen von Schiff beamen'));

            }

        }

        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/shipmacros.xhtml/entity_not_available');

        if ($target === null || $ship->canInteractWith($target, $isColony) === false) {
            return;
        }

        if ($target->getUser() !== $ship->getUser())
        {
            return;
        }

        $game->setMacro('html/shipmacros.xhtml/show_troop_transfer');

        $game->setTemplateVar('target', $target);
        $game->setTemplateVar('MAXIMUM', $max);
        $game->setTemplateVar('IS_UNLOAD', $isUnload);
        $game->setTemplateVar('IS_COLONY', $isColony);
    }
}
