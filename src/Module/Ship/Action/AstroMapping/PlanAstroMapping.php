<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AstroMapping;

use Override;
use request;

use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\AstronomicalEntryInterface;
use Stu\Orm\Entity\MapRegionInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class PlanAstroMapping implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_PLAN_ASTRO';

    private const int REGION_MAP_PERCENTAGE = 25;

    public function __construct(private ShipLoaderInterface $shipLoader, private StarSystemMapRepositoryInterface $starSystemMapRepository, private MapRepositoryInterface $mapRepository, private AstroEntryRepositoryInterface $astroEntryRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $system = $ship->getSystem();
        $mapRegion = $ship->getMapRegion();
        if ($system === null && $mapRegion === null) {
            return;
        }

        if ($this->astroEntryRepository->getByShipLocation($ship, false) !== null) {
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
        $astroEntry->setState(AstronomicalMappingEnum::PLANNED);
        $this->obtainMeasurementFields($system, $mapRegion, $astroEntry);

        $this->astroEntryRepository->save($astroEntry);

        $game->setView(ShowShip::VIEW_IDENTIFIER);
        $game->addInformation("Kartographie-Messpunkte wurden ermittelt");
    }


    private function obtainMeasurementFields(
        ?StarSystemInterface $system,
        ?MapRegionInterface $mapRegion,
        AstronomicalEntryInterface $entry
    ): void {
        if ($system !== null) {
            $entry->setSystem($system);
            $this->obtainMeasurementFieldsForSystem($system, $entry);
        }
        if ($mapRegion !== null) {
            $entry->setRegion($mapRegion);
            $this->obtainMeasurementFieldsForRegion($mapRegion, $entry);
        }
    }

    private function obtainMeasurementFieldsForSystem(StarSystemInterface $system, AstronomicalEntryInterface $entry): void
    {
        $idArray = $this->starSystemMapRepository->getRandomSystemMapIdsForAstroMeasurement($system->getId());

        $entry->setFieldIds(serialize($idArray));
    }

    private function obtainMeasurementFieldsForRegion(MapRegionInterface $mapRegion, AstronomicalEntryInterface $entry): void
    {
        $mapIds = $this->mapRepository->getRandomMapIdsForAstroMeasurement($mapRegion->getId(), self::REGION_MAP_PERCENTAGE);

        $entry->setFieldIds(serialize($mapIds));
    }


    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
