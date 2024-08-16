<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

use JBBCode\Parser;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

class StateIconAndTitle
{
    public function __construct(
        private GameControllerInterface $game,
        private Parser $bbCodeParser
    ) {}

    /**
     * @return array<string>|null
     */
    public function getStateIconAndTitle(ShipWrapperInterface $wrapper): ?array
    {
        $ship = $wrapper->get();
        $state = $ship->getState();

        if ($state === ShipStateEnum::SHIP_STATE_REPAIR_ACTIVE) {
            $isBase = $ship->isBase();
            return ['rep2', sprintf('%s repariert die Station', $isBase ? 'Stationscrew' : 'Schiffscrew')];
        }

        if ($state === ShipStateEnum::SHIP_STATE_REPAIR_PASSIVE) {
            $isBase = $ship->isBase();
            $repairDuration = $wrapper->getRepairDuration();
            return ['rep2', sprintf('%s wird repariert (noch %d Runden)', $isBase ? 'Station' : 'Schiff', $repairDuration)];
        }

        $currentTurn = $this->game->getCurrentRound()->getTurn();
        $astroLab = $wrapper->getAstroLaboratorySystemData();
        if (
            $state === ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING
            && $astroLab !== null
        ) {
            return ['map1', sprintf(
                'Schiff kartographiert (noch %d Runden)',
                $astroLab->getAstroStartTurn() + AstronomicalMappingEnum::TURNS_TO_FINISH - $currentTurn
            )];
        }

        $takeover = $ship->getTakeoverActive();
        if (
            $state === ShipStateEnum::SHIP_STATE_ACTIVE_TAKEOVER
            && $takeover !== null
        ) {
            $targetNamePlainText = $this->bbCodeParser->parse($takeover->getTargetShip()->getName())->getAsText();
            return ['take2', sprintf(
                'Schiff übernimmt die "%s" (noch %d Runden)',
                $targetNamePlainText,
                $wrapper->getTakeoverTicksLeft($takeover)
            )];
        }

        $takeover = $ship->getTakeoverPassive();
        if ($takeover !== null) {
            $sourceUserNamePlainText = $this->bbCodeParser->parse($takeover->getSourceShip()->getUser()->getName())->getAsText();
            return ['untake2', sprintf(
                'Schiff wird von Spieler "%s" übernommen (noch %d Runden)',
                $sourceUserNamePlainText,
                $wrapper->getTakeoverTicksLeft($takeover)
            )];
        }

        return null;
    }
}
