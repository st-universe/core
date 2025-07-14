<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\PostFlight;

use Override;
use Stu\Component\Ship\AstronomicalMappingStateEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Spacecraft\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;

class PostFlightAstroMappingConsequence extends AbstractFlightConsequence implements PostFlightConsequenceInterface
{
    public function __construct(
        private AstroEntryRepositoryInterface $astroEntryRepository,
        private AstroEntryLibInterface $astroEntryLib,
        private CreatePrestigeLogInterface $createPrestigeLog,
        private MessageFactoryInterface $messageFactory
    ) {}

    #[Override]
    protected function skipWhenTractored(): bool
    {
        return false;
    }

    #[Override]
    protected function triggerSpecific(
        SpacecraftWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        MessageCollectionInterface $messages
    ): void {

        if (!$wrapper instanceof ShipWrapperInterface) {
            return;
        }

        $ship = $wrapper->get();

        if (!$ship->getAstroState()) {
            return;
        }

        if ($ship->getSystem() === null && $ship->getMapRegion() === null) {
            return;
        }

        $astroEntry = $this->astroEntryLib->getAstroEntryByShipLocation($ship, false);
        if ($astroEntry === null) {
            return;
        }

        $fieldId = $ship->getLocation()->getId();

        $message = $this->messageFactory->createMessage(null, $ship->getUser()->getId());
        $messages->add($message);

        if ($astroEntry->getState() === AstronomicalMappingStateEnum::PLANNED) {
            /** @var array<int> */
            $idsToMap = unserialize($astroEntry->getFieldIds());

            $key = array_search($fieldId, $idsToMap);

            if (is_int($key)) {
                unset($idsToMap[$key]);

                if (empty($idsToMap)) {
                    $astroEntry->setFieldIds('');
                    $astroEntry->setState(AstronomicalMappingStateEnum::MEASURED);
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
            $ship->getState() === SpacecraftStateEnum::ASTRO_FINALIZING
            && $astroEntry->getState() === AstronomicalMappingStateEnum::FINISHING
            && $astroLab !== null
        ) {
            $ship->getCondition()->setState(SpacecraftStateEnum::NONE);
            $astroLab->setAstroStartTurn(null)->update();
            $astroEntry->setState(AstronomicalMappingStateEnum::MEASURED);
            $astroEntry->setAstroStartTurn(null);
            $message->add(sprintf(_('Die %s hat das Finalisieren der Kartographierung abgebrochen'), $ship->getName()));
        }

        $this->astroEntryRepository->save($astroEntry);
    }

    private function createPrestigeLog(Ship $ship): void
    {
        $this->createPrestigeLog->createLog(
            1,
            sprintf('1 Prestige erhalten für Kartographierungs-Messpunkt "%s"', $ship->getSectorString()),
            $ship->getUser(),
            time()
        );
    }

    private function addReachedWaypointInfo(MessageInterface $message, Ship $ship): void
    {
        $message->add(sprintf(
            _('Die %s hat einen Kartographierungs-Messpunkt erreicht: %s'),
            $ship->getName(),
            $ship->getSectorString()
        ));
    }
}
