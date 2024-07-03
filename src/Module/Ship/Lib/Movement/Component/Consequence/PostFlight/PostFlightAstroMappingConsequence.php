<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\PostFlight;

use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\Message\MessageFactoryInterface;
use Stu\Module\Ship\Lib\Message\MessageInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;

class PostFlightAstroMappingConsequence extends AbstractFlightConsequence
{
    public function __construct(
        private AstroEntryRepositoryInterface $astroEntryRepository,
        private CreatePrestigeLogInterface $createPrestigeLog,
        private MessageFactoryInterface $messageFactory
    ) {
    }

    protected function triggerSpecific(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        $ship = $wrapper->get();

        if (!$ship->getAstroState()) {
            return;
        }

        if ($ship->getSystem() === null && $ship->getMapRegion() === null) {
            return;
        }

        $astroEntry = $this->astroEntryRepository->getByShipLocation($ship, false);
        if ($astroEntry === null) {
            return;
        }

        $fieldId = $ship->getCurrentMapField()->getId();

        $message = $this->messageFactory->createMessage(null, $ship->getUser()->getId());
        $messages->add($message);

        if ($astroEntry->getState() === AstronomicalMappingEnum::PLANNED) {
            /** @var array<int> */
            $idsToMap = unserialize($astroEntry->getFieldIds());

            $key = array_search($fieldId, $idsToMap);

            if (is_int($key)) {
                unset($idsToMap[$key]);

                if (empty($idsToMap)) {
                    $astroEntry->setFieldIds('');
                    $astroEntry->setState(AstronomicalMappingEnum::MEASURED);
                    $message->add(sprintf(_('Die %s hat alle Kartographierungs-Messpunkte erreicht'), $ship->getName()));
                } else {
                    $astroEntry->setFieldIds(serialize($idsToMap));
                    $this->addReachedWaypointInfo($message, $ship);
                }

                $this->createPrestigeLog($ship);
            }
        }

        $astroLab = $wrapper->getAstroLaboratorySystemData();

        if (
            $ship->getState() === ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING
            && $astroEntry->getState() === AstronomicalMappingEnum::FINISHING
            && $astroLab !== null
        ) {
            $ship->setState(ShipStateEnum::SHIP_STATE_NONE);
            $astroLab->setAstroStartTurn(null)->update();
            $astroEntry->setState(AstronomicalMappingEnum::MEASURED);
            $astroEntry->setAstroStartTurn(null);
            $message->add(sprintf(_('Die %s hat das Finalisieren der Kartographierung abgebrochen'), $ship->getName()));
        }

        $this->astroEntryRepository->save($astroEntry);
    }

    private function createPrestigeLog(ShipInterface $ship): void
    {
        $this->createPrestigeLog->createLog(
            1,
            sprintf('1 Prestige erhalten für Kartographierungs-Messpunkt "%s"', $ship->getSectorString()),
            $ship->getUser(),
            time()
        );
    }

    private function addReachedWaypointInfo(MessageInterface $message, ShipInterface $ship): void
    {
        $message->add(sprintf(
            _('Die %s hat einen Kartographierungs-Messpunkt erreicht: %s'),
            $ship->getName(),
            $ship->getSectorString()
        ));
    }
}
