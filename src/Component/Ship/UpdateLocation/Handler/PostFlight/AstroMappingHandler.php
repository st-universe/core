<?php

declare(strict_types=1);

namespace Stu\Component\Ship\UpdateLocation\Handler\PostFlight;

use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\UpdateLocation\Handler\AbstractUpdateLocationHandler;
use Stu\Component\Ship\UpdateLocation\Handler\UpdateLocationHandlerInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class AstroMappingHandler extends AbstractUpdateLocationHandler implements UpdateLocationHandlerInterface
{
    private AstroEntryRepositoryInterface $astroEntryRepository;

    private AstroEntryLibInterface $astroEntryLib;

    private MapRepositoryInterface $mapRepository;

    public function __construct(
        AstroEntryRepositoryInterface $astroEntryRepository,
        MapRepositoryInterface $mapRepository,
        AstroEntryLibInterface $astroEntryLib
    ) {
        $this->astroEntryRepository = $astroEntryRepository;
        $this->astroEntryLib = $astroEntryLib;
        $this->mapRepository = $mapRepository;
    }

    public function handle(ShipWrapperInterface $wrapper, ?ShipInterface $tractoringShip): void
    {
        $ship = $wrapper->get();
        if (!$ship->getAstroState()) {
            return;
        }

        // cancel active finalizing
        if ($ship->getState() === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
            $this->astroEntryLib->cancelAstroFinalizing($ship);
            $this->addMessageInternal(sprintf(_('Die %s hat die Kartographierungs-Finalisierung abgebrochen'), $ship->getName()));
            return;
        }
        if ($ship->getSystem() != null) {
            $astroEntry = $this->astroEntryRepository->getByUserAndSystem($ship->getUser()->getId(), $ship->getSystemsId());

            if ($astroEntry === null) {
                return;
            }

            // check for finished waypoints
            $currentField = $ship->getStarsystemMap();
            if ($astroEntry->getState() == AstronomicalMappingEnum::PLANNED) {
                if ($astroEntry->getStarsystemMap1() === $currentField) {
                    $astroEntry->setStarsystemMap1(null);
                    $this->addMessageInternal(sprintf(_('Die %s hat einen Kartographierungs-Messpunkt erreicht (%d|%d)'), $ship->getName(), $ship->getPosX(), $ship->getPosY()));
                } elseif ($astroEntry->getStarsystemMap2() === $currentField) {
                    $astroEntry->setStarsystemMap2(null);
                    $this->addMessageInternal(sprintf(_('Die %s hat einen Kartographierungs-Messpunkt erreicht (%d|%d)'), $ship->getName(), $ship->getPosX(), $ship->getPosY()));
                } elseif ($astroEntry->getStarsystemMap3() === $currentField) {
                    $astroEntry->setStarsystemMap3(null);
                    $this->addMessageInternal(sprintf(_('Die %s hat einen Kartographierungs-Messpunkt erreicht (%d|%d)'), $ship->getName(), $ship->getPosX(), $ship->getPosY()));
                } elseif ($astroEntry->getStarsystemMap4() === $currentField) {
                    $astroEntry->setStarsystemMap4(null);
                    $this->addMessageInternal(sprintf(_('Die %s hat einen Kartographierungs-Messpunkt erreicht (%d|%d)'), $ship->getName(), $ship->getPosX(), $ship->getPosY()));
                } elseif ($astroEntry->getStarsystemMap5() === $currentField) {
                    $astroEntry->setStarsystemMap5(null);
                    $this->addMessageInternal(sprintf(_('Die %s hat einen Kartographierungs-Messpunkt erreicht (%d|%d)'), $ship->getName(), $ship->getPosX(), $ship->getPosY()));
                }

                if ($astroEntry->isMeasured()) {
                    $astroEntry->setState(AstronomicalMappingEnum::MEASURED);
                    $this->addMessageInternal(sprintf(_('Die %s hat alle Kartographierungs-Messpunkte erreicht'), $ship->getName()));
                }
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
                        foreach ($array  as $item) {
                            if (is_array($item) && isset($item['id']) && is_int($item['id'])) {
                                $result = $this->mapRepository->getById($item['id']);
                                $results[] = $result;
                            }
                        }
                        if (in_array($currentField, $results)) {
                            $results = array_filter($results, function ($item) use ($currentField) {
                                return $item !== $currentField;
                            });
                            $idArray = array_map(function ($item) {
                                return $item->getId();
                            }, $results);
                            $astroEntry->setRegionFields(serialize($idArray));
                            $this->astroEntryRepository->save($astroEntry);
                        }
                    }
                }
            }
        }
    }
}
