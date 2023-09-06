<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Lib\InformationWrapper;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class CheckAstronomicalWaypoints implements CheckAstronomicalWaypointsInterface
{
    private AstroEntryRepositoryInterface $astroEntryRepository;

    private MapRepositoryInterface $mapRepository;

    public function __construct(
        AstroEntryRepositoryInterface $astroEntryRepository,
        MapRepositoryInterface $mapRepository
    ) {
        $this->astroEntryRepository = $astroEntryRepository;
        $this->mapRepository = $mapRepository;
    }

    public function checkWaypoint(
        ShipInterface $ship,
        StarSystemMapInterface|MapInterface $nextField,
        InformationWrapper $informations
    ): void {
        if (!$ship->getAstroState()) {
            return;
        }

        if ($ship->getSystem() != null) {

            $astroEntry = $this->astroEntryRepository->getByUserAndSystem($ship->getUser()->getId(), $ship->getSystemsId());

            if ($astroEntry === null) {
                return;
            }

            if ($astroEntry->getState() == AstronomicalMappingEnum::PLANNED) {
                if ($astroEntry->getStarsystemMap1() === $nextField) {
                    $astroEntry->setStarsystemMap1(null);
                    $this->addReachedWaypointInfo($informations, $ship);
                } elseif ($astroEntry->getStarsystemMap2() === $nextField) {
                    $astroEntry->setStarsystemMap2(null);
                    $this->addReachedWaypointInfo($informations, $ship);
                } elseif ($astroEntry->getStarsystemMap3() === $nextField) {
                    $astroEntry->setStarsystemMap3(null);
                    $this->addReachedWaypointInfo($informations, $ship);
                } elseif ($astroEntry->getStarsystemMap4() === $nextField) {
                    $astroEntry->setStarsystemMap4(null);
                    $this->addReachedWaypointInfo($informations, $ship);
                } elseif ($astroEntry->getStarsystemMap5() === $nextField) {
                    $astroEntry->setStarsystemMap5(null);
                    $this->addReachedWaypointInfo($informations, $ship);
                }

                if ($astroEntry->isMeasured()) {
                    $astroEntry->setState(AstronomicalMappingEnum::MEASURED);
                    $informations->addInformation(sprintf(_('Die %s hat alle Kartographierungs-Messpunkte erreicht'), $ship->getName()));
                }
            }

            if (
                $ship->getState() === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING
                && $astroEntry->getState() === AstronomicalMappingEnum::FINISHING
            ) {
                $ship->setState(ShipStateEnum::SHIP_STATE_NONE);
                $ship->setAstroStartTurn(null);
                $astroEntry->setState(AstronomicalMappingEnum::MEASURED);
                $astroEntry->setAstroStartTurn(null);
                $informations->addInformation(sprintf(_('Die %s hat das Finalisieren der Kartographierung abgebrochen'), $ship->getName()));
            }

            $this->astroEntryRepository->save($astroEntry);
        }


        if ($ship->getMap() != null) {
            if ($ship->getMap()->getMapRegion() != null) {

                $currentField = $ship->getMap();
                $astroEntry = $this->astroEntryRepository->getByUserAndRegion($ship->getUser()->getId(), $ship->getMap()->getMapRegion()->getId());

                if ($astroEntry === null) {
                    return;
                }
                if ($astroEntry->getState() == AstronomicalMappingEnum::PLANNED) {
                    if ($astroEntry->getRegionFields() !== null) {
                        $array = unserialize($astroEntry->getRegionFields());
                        $results = [];
                        foreach ($array as $item) {
                            if (is_array($item) && isset($item['id']) && is_int($item['id'])) {
                                $result = $this->mapRepository->getById($item['id']);
                                $results[] = $result;
                            }
                        }

                        if (in_array($currentField, $results)) {
                            $results = array_filter($results, function ($item) use ($currentField) {
                                return $item !== $currentField;
                            });

                            // Überprüfen, ob der letzte Eintrag entfernt wurde
                            if (count($results) === 0) {
                                $astroEntry->setRegionFields(null);
                                $astroEntry->setState(AstronomicalMappingEnum::MEASURED);
                                $informations->addInformation(sprintf(_('Die %s hat alle Kartographierungs-Messpunkte erreicht'), $ship->getName()));
                            } else {
                                $idArray = array_map(function ($item) {
                                    return ['id' => $item->getId()];
                                }, $results);
                                $astroEntry->setRegionFields(serialize($idArray));
                            }

                            $this->astroEntryRepository->save($astroEntry);
                            $this->addReachedWaypointInfo($informations, $ship);
                        }
                    }
                }
            }
        }
    }
    private function addReachedWaypointInfo(InformationWrapper $informations, ShipInterface $ship): void
    {
        $informations->addInformation(sprintf(
            _('Die %s hat einen Kartographierungs-Messpunkt erreicht (%d|%d)'),
            $ship->getName(),
            $ship->getPosX(),
            $ship->getPosY()
        ));
    }
}
