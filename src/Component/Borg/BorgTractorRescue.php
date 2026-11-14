<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Interaction\InteractionCheckerBuilderFactoryInterface;
use Stu\Lib\Interaction\InteractionCheckType;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;

final class BorgTractorRescue
{
    private BorgTractorRescueSalvos $salvos;

    public function __construct(
        private BorgSphereRepositoryInterface $events,
        private BorgSphereCombat $combat,
        private BorgTractorRescueResponse $response,
        BorgAssimilationLifecycle $assimilation,
        SpacecraftSystemManagerInterface $systems,
        private InteractionCheckerBuilderFactoryInterface $checks,
        StuRandom $random,
        private BorgTractorRescueParticipants $participants
    ) {
        $this->salvos = new BorgTractorRescueSalvos($assimilation, $systems, $random);
    }

    public function attack(SpacecraftWrapperInterface $source, SpacecraftWrapperInterface $target, InformationInterface $information): bool
    {
        $ship = $source->get();
        $cube = $target->get();
        $event = $this->events->getByShip($cube);
        if (!$ship instanceof Ship || !$cube instanceof Ship || $event === null || !$this->isAvailable($event, $cube)) {
            $information->addInformation('Hier ist kein assimilierender Borg-Traktorstrahl erreichbar');
            return false;
        }
        if (!$this->checks->createInteractionChecker()->setSource($ship)->setTarget($cube)->setCheckTypes([
            InteractionCheckType::EXPECT_SOURCE_ENABLED,
            InteractionCheckType::EXPECT_SOURCE_UNCLOAKED,
            InteractionCheckType::EXPECT_SOURCE_UNWARPED,
            InteractionCheckType::EXPECT_TARGET_UNWARPED,
            InteractionCheckType::EXPECT_TARGET_ON_SAME_SIDE_OF_FINISHED_WEB
        ])->check($information)) {
            return false;
        }
        $this->events->lockEvent($event);
        if (!$this->isAvailable($event, $cube)) {
            return false;
        }
        $participants = $this->participants->getParticipants($source, $cube);
        if ($participants === []) {
            $information->addInformation('Für die Frequenzmodulation werden einsatzbereite Energiewaffen, Nahbereichssensoren, ausreichend Crew und Energie benötigt');
            return false;
        }
        $this->combat->withLockedEvent($cube, $event, function () use ($event, $participants, $target, $information, $cube, $ship): void {
            $this->salvos->fire($event, $participants, $target, $information);
            $this->combat->registerHit($cube);
            $this->response->respond($event, $cube, $ship, $information);
        });
        return true;
    }

    private function isAvailable(BorgSphereEvent $event, Ship $cube): bool
    {
        return !$event->isFinished() && !$cube->getCondition()->isDestroyed() && !$cube->getCondition()->isDisabled()
            && ($event->getConfiguration()->getSettings()['assimilation_enabled'] ?? 0) === 1
            && $cube->getTractoredShip()?->isBeingAssimilated() === true
            && $cube->getSystemState(SpacecraftSystemTypeEnum::TRACTOR_BEAM);
    }
}
