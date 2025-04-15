<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowShip;

use Override;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Game\ModuleEnum;
use Stu\Module\Control\ViewContext;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Spacecraft\View\ShowSpacecraft\SpacecraftTypeShowStragegyInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;

class ShipShowStrategy implements SpacecraftTypeShowStragegyInterface
{
    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private DatabaseUserRepositoryInterface $databaseUserRepository,
        private AstroEntryLibInterface $astroEntryLib,
    ) {}

    #[Override]
    public function appendNavigationPart(GameControllerInterface $game): SpacecraftTypeShowStragegyInterface
    {
        $game->appendNavigationPart('ship.php',  _('Schiffe'));

        return $this;
    }

    #[Override]
    public function setTemplateVariables(int $spacecraftId, GameControllerInterface $game): SpacecraftTypeShowStragegyInterface
    {
        $ship = $this->shipLoader->getByIdAndUser($spacecraftId, $game->getUser()->getId(), true, false);
        $game->setTemplateVar('ASTRO_STATE_SYSTEM', $this->getAstroState($ship, $game, true));
        $game->setTemplateVar('ASTRO_STATE_REGION', $this->getAstroState($ship, $game, false));

        return $this;
    }

    private function getAstroState(ShipInterface $ship, GameControllerInterface $game, bool $isSystem): AstroStateWrapper
    {
        //$this->loggerUtil->init('SS', LoggerEnum::LEVEL_ERROR);

        $databaseEntry = $this->getDatabaseEntryForShipLocation($ship, $isSystem);

        $astroEntry = null;

        if ($databaseEntry === null) {
            $state = AstronomicalMappingEnum::NONE;
        } elseif ($this->databaseUserRepository->exists($game->getUser()->getId(), $databaseEntry->getId())) {
            $state = AstronomicalMappingEnum::DONE;
        } else {
            $astroEntry = $this->astroEntryLib->getAstroEntryByShipLocation($ship, $isSystem);

            $state = $astroEntry === null ? AstronomicalMappingEnum::PLANNABLE : $astroEntry->getState();
        }
        $turnsLeft = null;
        if ($state === AstronomicalMappingEnum::FINISHING && $astroEntry !== null) {
            $turnsLeft = AstronomicalMappingEnum::TURNS_TO_FINISH - ($game->getCurrentRound()->getTurn() - $astroEntry->getAstroStartTurn());
        }
        $measurementpointsleft = null;
        if ($state === AstronomicalMappingEnum::PLANNED && $astroEntry !== null) {
            $fieldIds = unserialize($astroEntry->getFieldIds());
            $measurementpointsleft = is_array($fieldIds) ? count($fieldIds) : 0;
        }


        $wrapper = new AstroStateWrapper($state, $turnsLeft, $isSystem, $measurementpointsleft);

        return $wrapper;
    }

    private function getDatabaseEntryForShipLocation(ShipInterface $ship, bool $isSystem): ?DatabaseEntryInterface
    {
        if ($isSystem) {
            $system = $ship->getSystem() ?? $ship->isOverSystem();
            if ($system !== null) {
                return $system->getDatabaseEntry();
            }

            return null;
        }

        $mapRegion = $ship->getMapRegion();
        if ($mapRegion !== null) {
            return $mapRegion->getDatabaseEntry();
        }

        return null;
    }

    #[Override]
    public function getViewContext(): ViewContext
    {
        return new ViewContext(ModuleEnum::SHIP, ShowSpacecraft::VIEW_IDENTIFIER);
    }
}
