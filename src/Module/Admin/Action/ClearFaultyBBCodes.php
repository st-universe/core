<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use JBBCode\Parser;
use Stu\Lib\CleanTextUtils;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\FleetRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ClearFaultyBBCodes implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CORRUPT_BBCODES';

    private ShipRepositoryInterface $shipRepository;

    private FleetRepositoryInterface $fleetRepository;

    private ColonyRepositoryInterface $colonyRepository;

    private AllianceRepositoryInterface $allianceRepository;

    private Parser $bbCodeParser;

    public function __construct(
        ShipRepositoryInterface $shipRepository,
        FleetRepositoryInterface $fleetRepository,
        ColonyRepositoryInterface $colonyRepository,
        AllianceRepositoryInterface $allianceRepository,
        Parser $bbCodeParser
    ) {
        $this->shipRepository = $shipRepository;
        $this->fleetRepository = $fleetRepository;
        $this->colonyRepository = $colonyRepository;
        $this->allianceRepository = $allianceRepository;
        $this->bbCodeParser = $bbCodeParser;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowTicks::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->getUser()->isAdmin()) {
            $game->addInformation(_('[b][color=FF2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        //SHIPS
        $game->addInformation("SHIPS:");
        $allShips = $this->shipRepository->findAll();
        foreach ($allShips as $ship) {
            if (!CleanTextUtils::checkBBCode($ship->getName())) {
                $game->addInformationf(_("ship_id: %d, name: %s"), $ship->getId(), $ship->getName());

                $textOnly = $this->bbCodeParser->parse($ship->getName())->getAsText();

                //$ship->setName($textOnly);
                //$this->shipRepository->save($ship);
            }
        }
        //$game->addInformation("Schiffsnamen wurde bereinigt!");

        //FLEETS
        $game->addInformation("FLEETS:");
        $allFleets = $this->fleetRepository->findAll();
        foreach ($allFleets as $fleet) {
            if (!CleanTextUtils::checkBBCode($fleet->getName())) {
                $game->addInformationf(_("fleet_id: %d, name: %s"), $fleet->getId(), $fleet->getName());

                $textOnly = $this->bbCodeParser->parse($fleet->getName())->getAsText();

                // $fleet->setName($textOnly);
                // $this->fleetRepository->save($fleet);
            }
        }
        //$game->addInformation("Flottennamen wurde bereinigt!");

        //COLONIES
        $game->addInformation("COLONIES:");
        $allColonies = $this->colonyRepository->findAll();
        foreach ($allColonies as $colony) {
            if (!CleanTextUtils::checkBBCode($colony->getName())) {
                $game->addInformationf(_("colony_id: %d, name: %s"), $colony->getId(), $colony->getName());

                $textOnly = $this->bbCodeParser->parse($colony->getName())->getAsText();

                // $colony->setName($textOnly);
                // $this->colonyRepository->save($colony);
            }
        }
        //$game->addInformation("Kolonienamen wurde bereinigt!");

        //ALLIANCES
        $game->addInformation("ALLIANCES:");
        $allAllys = $this->allianceRepository->findAll();
        foreach ($allAllys as $ally) {
            if (!CleanTextUtils::checkBBCode($ally->getName())) {
                $game->addInformationf(_("alliance_id: %d, name: %s"), $ally->getId(), $ally->getName());

                $textOnly = $this->bbCodeParser->parse($ally->getName())->getAsText();

                //$ally->setName($textOnly);
                // $this->allianceRepository->save($ally);
            }
        }
        //$game->addInformation("Allianznamen wurde bereinigt!");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
