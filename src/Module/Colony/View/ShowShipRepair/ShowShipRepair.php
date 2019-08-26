<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowShipRepair;

use Colfields;
use request;
use RumpBuildingFunction;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class ShowShipRepair implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP_REPAIR';

    private $colonyLoader;

    private $showShipRepairRequest;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowShipRepairRequestInterface $showShipRepairRequest
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showShipRepairRequest = $showShipRepairRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showShipRepairRequest->getColonyId(),
            $userId
        );

        $fieldId = (int)request::indInt('fid');

        $field = Colfields::getByColonyField(
            $this->showShipRepairRequest->getFieldId(),
            $colony->getId()
        );

        if ($colony->hasShipyard()) {

            $repairableShips = [];
            foreach ($colony->getOrbitShipList() as $fleet) {
                foreach ($fleet['ships'] as $ship_id => $ship) {
                    if (!$ship->canBeRepaired() || $ship->getState() == SHIP_STATE_REPAIR) {
                        continue;
                    }
                    foreach (RumpBuildingFunction::getByRumpId($ship->getRumpId()) as $rump_rel) {
                        if ($field->getBuilding()->hasFunction($rump_rel->getBuildingFunction())) {
                            $repairableShips[$ship->getId()] = $ship;
                            break;
                        }
                    }
                }
            }

            $game->appendNavigationPart(
                'colony.php',
                _('Kolonien')
            );
            $game->appendNavigationPart(
                sprintf('?%s=1&id=%d',
                    ShowColony::VIEW_IDENTIFIER,
                    $colony->getId()
                ),
                $colony->getNameWithoutMarkup()
            );
            $game->appendNavigationPart(
                sprintf(
                    '?id=%s&%d=1&fid=%d',
                    $colony->getId(),
                    static::VIEW_IDENTIFIER,
                    $field->getFieldId()
                ),
                _('Schiffreparatur')
            );
            $game->setPagetitle(_('Schiffreparatur'));
            $game->setTemplateFile('html/colony_shiprepair.xhtml');

            $game->setTemplateVar('REPAIRABLE_SHIP_LIST', $repairableShips);
            $game->setTemplateVar('COLONY', $colony);
            $game->setTemplateVar('FIELD', $field);
        }
    }
}
