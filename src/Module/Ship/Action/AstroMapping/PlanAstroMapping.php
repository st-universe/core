<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AstroMapping;

use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\StarSystemMap;
use Override;
use request;

use Stu\Component\Ship\AstronomicalMappingStateEnum;
use Stu\Component\Spacecraft\SpacecraftLssModeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\AstronomicalEntry;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\MapRegion;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class PlanAstroMapping implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_PLAN_ASTRO';

    private const int REGION_MAP_PERCENTAGE = 25;

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private StarSystemMapRepositoryInterface $starSystemMapRepository,
        private MapRepositoryInterface $mapRepository,
        private AstroEntryRepositoryInterface $astroEntryRepository,
        private AstroEntryLibInterface $astroEntryLib,
        private ActivatorDeactivatorHelperInterface $helper
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $ship = $wrapper->get();
        $system = $ship->getSystem();
        $mapRegion = $ship->getMapRegion();
        if ($system === null && $mapRegion === null) {
            return;
        }

        if ($this->astroEntryLib->getAstroEntryByShipLocation($ship, false) !== null) {
            return;
        }

        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        // system needs to be active
        if (!$ship->getAstroState()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, das Astrometrische Labor muss aktiviert sein![/color][/b]'));
            return;
        }

        $astroEntry = $this->astroEntryRepository->prototype();
        $astroEntry->setUser($game->getUser());
        $astroEntry->setState(AstronomicalMappingStateEnum::PLANNED);
        $this->obtainMeasurementFields($system, $mapRegion, $astroEntry, $ship->getLocation());

        $this->astroEntryRepository->save($astroEntry);

        $lss = $wrapper->getLssSystemData();

        if ($lss !== null && $lss->getMode() !== SpacecraftLssModeEnum::CARTOGRAPHING) {
            $this->helper->setLssMode($ship->getId(), SpacecraftLssModeEnum::CARTOGRAPHING, $game);
        }

        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);
        $game->addInformation("Kartographie-Messpunkte wurden ermittelt");
    }


    private function obtainMeasurementFields(
        ?StarSystem $system,
        ?MapRegion $mapRegion,
        AstronomicalEntry $entry,
        Map|StarSystemMap $location
    ): void {
        if ($system !== null) {
            $entry->setSystem($system);
            $this->obtainMeasurementFieldsForSystem($system, $entry, $location);
        }
        if ($mapRegion !== null) {
            $entry->setRegion($mapRegion);
            $this->obtainMeasurementFieldsForRegion($mapRegion, $entry, $location);
        }
    }

    private function obtainMeasurementFieldsForSystem(StarSystem $system, AstronomicalEntry $entry, Location $location): void
    {
        $idArray = $this->starSystemMapRepository->getRandomSystemMapIdsForAstroMeasurement($system->getId(), $location->getFieldId());

        $entry->setFieldIds(serialize($idArray));
    }

    private function obtainMeasurementFieldsForRegion(MapRegion $mapRegion, AstronomicalEntry $entry, Location $location): void
    {
        $mapIds = $this->mapRepository->getRandomMapIdsForAstroMeasurement($mapRegion->getId(), self::REGION_MAP_PERCENTAGE, $location->getFieldId());

        $entry->setFieldIds(serialize($mapIds));
    }


    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
