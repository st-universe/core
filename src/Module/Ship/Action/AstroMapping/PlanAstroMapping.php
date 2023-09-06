<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\AstroMapping;

use request;

use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\AstronomicalEntryInterface;
use Stu\Orm\Proxy\__CG__\Stu\Orm\Entity\Map;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class PlanAstroMapping implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_PLAN_ASTRO';

    private ShipLoaderInterface $shipLoader;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    private AstroEntryRepositoryInterface $astroEntryRepository;

    private MapRepositoryInterface $mapRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        StarSystemMapRepositoryInterface $starSystemMapRepository,
        MapRepositoryInterface $mapRepository,
        AstroEntryRepositoryInterface $astroEntryRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->starSystemMapRepository = $starSystemMapRepository;
        $this->mapRepository = $mapRepository;
        $this->astroEntryRepository = $astroEntryRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if ($ship->getMap()->getMapRegion() === null && $ship->getSystem() === null) {
            return;
        }

        if ($ship->getSystem() != null) {


            if ($this->astroEntryRepository->getByUserAndSystem($userId, $ship->getSystemsId()) !== null) {
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
            $astroEntry->setSystem($ship->getSystem());
            $this->obtainMeasurementFields($astroEntry);

            $this->astroEntryRepository->save($astroEntry);

            $game->setView(ShowShip::VIEW_IDENTIFIER);
            $game->addInformation("Kartographie-Messpunkte wurden ermittelt");
        }
        if ($ship->getMap()) {
            if ($ship->getMap()->getMapRegion()) {

                if ($this->astroEntryRepository->getByUserAndRegion($userId, $ship->getMap()->getRegionId()) !== null) {
                    return;
                }
                $astroEntry = $this->astroEntryRepository->prototype();
                $astroEntry->setUser($game->getUser());
                $astroEntry->setState(AstronomicalMappingEnum::PLANNED);
                $astroEntry->setRegion($ship->getMap()->getMapRegion());
                $this->obtainMeasurementRegionFields($astroEntry);
                $this->astroEntryRepository->save($astroEntry);
                $game->setView(ShowShip::VIEW_IDENTIFIER);
                $game->addInformation("Kartographie-Messpunkte wurden ermittelt");
            }
        }
    }


    private function obtainMeasurementFields(AstronomicalEntryInterface $entry): void
    {
        $idArray = $this->starSystemMapRepository->getRandomFieldsForAstroMeasurement($entry->getSystem()->getId());

        $entry->setStarsystemMap1($this->starSystemMapRepository->find($idArray[0]['id']));
        $entry->setStarsystemMap2($this->starSystemMapRepository->find($idArray[1]['id']));
        $entry->setStarsystemMap3($this->starSystemMapRepository->find($idArray[2]['id']));
        $entry->setStarsystemMap4($this->starSystemMapRepository->find($idArray[3]['id']));
        $entry->setStarsystemMap5($this->starSystemMapRepository->find($idArray[4]['id']));
    }

    private function obtainMeasurementRegionFields(AstronomicalEntryInterface $entry): void
    {
        if ($entry->getRegion() != null) {
            $array = $this->mapRepository->getRandomFieldsForAstroMeasurement($entry->getRegion()->getId());
            if (count($array) > 1) {
                $removeCount = max(0, count($array) - ceil(count($array) * 0.25));
                shuffle($array);
                for ($i = 0; $i < $removeCount; $i++) {
                    array_pop($array);
                }
            }

            $entry->setRegionFields(serialize($array));
        }
    }


    public function performSessionCheck(): bool
    {
        return true;
    }
}
