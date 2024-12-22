<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Ui;

use JBBCode\Parser;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

class StateIconAndTitle
{
    public function __construct(
        private GameControllerInterface $game,
        private Parser $bbCodeParser
    ) {}

    /**
     * @return array<string>|null
     */
    public function getStateIconAndTitle(SpacecraftWrapperInterface $wrapper): ?array
    {
        $spacecraft = $wrapper->get();
        $state = $spacecraft->getState();

        if ($state === SpacecraftStateEnum::SHIP_STATE_RETROFIT) {
            return ['buttons/konstr1', 'Schiff wird umgerüstet'];
        }

        if ($state === SpacecraftStateEnum::SHIP_STATE_REPAIR_ACTIVE) {
            $isStation = $spacecraft->isStation();
            return ['buttons/rep2', sprintf('%s repariert die Station', $isStation ? 'Stationscrew' : 'Schiffscrew')];
        }

        if ($state === SpacecraftStateEnum::SHIP_STATE_REPAIR_PASSIVE) {
            $isStation = $spacecraft->isStation();
            $repairDuration = $wrapper->getRepairDuration();
            return ['buttons/rep2', sprintf('%s wird repariert (noch %d Runden)', $isStation ? 'Station' : 'Schiff', $repairDuration)];
        }

        $astroLab = $wrapper instanceof ShipWrapperInterface ? $wrapper->getAstroLaboratorySystemData() : null;
        if (
            $state === SpacecraftStateEnum::SHIP_STATE_ASTRO_FINALIZING
            && $astroLab !== null
        ) {
            return ['buttons/map1', sprintf(
                'Schiff kartographiert (noch %d Runden)',
                $astroLab->getAstroStartTurn() + AstronomicalMappingEnum::TURNS_TO_FINISH - $this->game->getCurrentRound()->getTurn()
            )];
        }

        $takeover = $spacecraft->getTakeoverActive();
        if (
            $state === SpacecraftStateEnum::SHIP_STATE_ACTIVE_TAKEOVER
            && $takeover !== null
        ) {
            $targetNamePlainText = $this->bbCodeParser->parse($takeover->getTargetSpacecraft()->getName())->getAsText();
            return ['buttons/take2', sprintf(
                'Schiff übernimmt die "%s" (noch %d Runden)',
                $targetNamePlainText,
                $wrapper->getTakeoverTicksLeft($takeover)
            )];
        }

        $takeover = $spacecraft->getTakeoverPassive();
        if ($takeover !== null) {
            $sourceUserNamePlainText = $this->bbCodeParser->parse($takeover->getSourceSpacecraft()->getUser()->getName())->getAsText();
            return ['buttons/untake2', sprintf(
                'Schiff wird von Spieler "%s" übernommen (noch %d Runden)',
                $sourceUserNamePlainText,
                $wrapper->getTakeoverTicksLeft($takeover)
            )];
        }

        if (
            $state === SpacecraftStateEnum::SHIP_STATE_GATHER_RESOURCES
            && $spacecraft instanceof ShipInterface
        ) {
            $miningqueue = $spacecraft->getMiningQueue();
            $module = $spacecraft->getSpacecraftSystem(SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR)->getModule();
            $gathercount = 0;
            if ($miningqueue !== null) {
                $locationmining = $miningqueue->getLocationMining();
                if ($module !== null) {
                    $gathercount = $module->getFactionId() == null ? 100 : 200;
                    return [sprintf('commodities/%s', $locationmining->getCommodity()->getId()), sprintf(
                        'Schiff sammelt Ressourcen (~%d %s/Tick)',
                        $gathercount,
                        $locationmining->getCommodity()->getName()
                    )];
                }
            }
        }

        return null;
    }
}
