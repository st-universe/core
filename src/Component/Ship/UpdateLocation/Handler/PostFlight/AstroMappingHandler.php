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

final class AstroMappingHandler extends AbstractUpdateLocationHandler implements UpdateLocationHandlerInterface
{
    private AstroEntryRepositoryInterface $astroEntryRepository;

    private AstroEntryLibInterface $astroEntryLib;

    public function __construct(
        AstroEntryRepositoryInterface $astroEntryRepository,
        AstroEntryLibInterface $astroEntryLib
    ) {
        $this->astroEntryRepository = $astroEntryRepository;
        $this->astroEntryLib = $astroEntryLib;
    }

    public function handle(ShipWrapperInterface $wrapper, ?ShipInterface $tractoringShip): void
    {
        $ship = $wrapper->get();

        if ($ship->getSystem() === null) {
            return;
        }

        if (!$ship->getAstroState()) {
            return;
        }

        // cancel active finalizing
        if ($ship->getState() === ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING) {
            $this->astroEntryLib->cancelAstroFinalizing($ship);
            $this->addMessageInternal(sprintf(_('Die %s hat die Kartographierungs-Finalisierung abgebrochen'), $ship->getName()));
            return;
        }

        $astroEntry = $this->astroEntryRepository->getByShipLocation($ship);

        if ($astroEntry === null) {
            return;
        }

        // check for finished waypoints
        if ($astroEntry->getState() == AstronomicalMappingEnum::PLANNED) {
            //USE CODE FROM CheckAstronomicalWaypoint

            if ($astroEntry->isMeasured()) {
                $astroEntry->setState(AstronomicalMappingEnum::MEASURED);
                $this->addMessageInternal(sprintf(_('Die %s hat alle Kartographierungs-Messpunkte erreicht'), $ship->getName()));
            }
        }

        $this->astroEntryRepository->save($astroEntry);
    }
}
