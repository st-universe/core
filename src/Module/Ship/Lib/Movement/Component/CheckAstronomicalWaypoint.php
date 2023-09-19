<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component;

use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Lib\InformationWrapper;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;

class CheckAstronomicalWaypoint implements CheckAstronomicalWaypointInterface
{
    protected AstroEntryRepositoryInterface $astroEntryRepository;

    public function __construct(AstroEntryRepositoryInterface $astroEntryRepository)
    {
        $this->astroEntryRepository = $astroEntryRepository;
    }

    public function checkWaypoint(
        ShipInterface $ship,
        InformationWrapper $informations
    ): void {
        if (!$ship->getAstroState()) {
            return;
        }

        $astroEntry = $this->astroEntryRepository->getByShipLocation($ship);
        if ($astroEntry === null) {
            return;
        }

        $fieldId = $ship->getCurrentMapField()->getId();

        if ($astroEntry->getState() === AstronomicalMappingEnum::PLANNED) {
            /** @var array<int> */
            $idsToMap = unserialize($astroEntry->getFieldIds());

            $key = array_search($fieldId, $idsToMap);

            if (is_integer($key)) {
                unset($idsToMap[$key]);

                if ($astroEntry->isMeasured()) {
                    $astroEntry->setFieldIds('');
                    $astroEntry->setState(AstronomicalMappingEnum::MEASURED);
                    $informations->addInformation(sprintf(_('Die %s hat alle Kartographierungs-Messpunkte erreicht'), $ship->getName()));
                } else {
                    $astroEntry->setFieldIds(serialize($idsToMap));
                    $this->addReachedWaypointInfo($informations, $ship);
                }
            }
        }

        if (
            $ship->getState() === ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING
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

    private function addReachedWaypointInfo(InformationWrapper $informations, ShipInterface $ship): void
    {
        $informations->addInformation(sprintf(
            _('Die %s hat einen Kartographierungs-Messpunkt erreicht: %s'),
            $ship->getName(),
            $ship->getSectorString()
        ));
    }
}
