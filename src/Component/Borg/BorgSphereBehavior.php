<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Ship;

final class BorgSphereBehavior
{
    public function __construct(private BorgSphereNavigation $navigation, private BorgSphereBattle $battle, private StuRandom $random, private BorgSphereInterception $interception, private ?BorgAssimilationService $assimilation = null) {}

    public function work(BorgSphereEvent $event, Ship $ship): void
    {
        if (!$this->canAct($event, $ship)) {
            return;
        }
        $configuration = $event->getConfiguration();
        $captureActive = $this->assimilation?->hasActiveCapture($ship) === true;
        if (!$captureActive && $this->navigation->evadeAnomaly($ship, $configuration)) {
            return;
        }
        $hadCaptureTargets = $this->assimilation?->hasTargets($event, $ship) === true;
        if ($hadCaptureTargets && $this->assimilation->tryCapture($event, $ship)) {
            return;
        }
        if ($this->battle->attackTargets($event, $ship)) {
            return;
        }
        if ($captureActive) {
            return;
        }
        if ($this->random->rand(1, 100) <= $configuration->getSetting('idle_chance_percent')) {
            $this->interception->interceptAndAttack($event, $ship);
            return;
        }
        $this->moveAndCapture($event, $ship, $hadCaptureTargets);
    }

    private function moveAndCapture(BorgSphereEvent $event, Ship $ship, bool $hadCaptureTargets): void
    {
        $location = $ship->getLocation();
        $this->move($event, $ship);
        if (!$hadCaptureTargets && $ship->getLocation() !== $location) {
            $this->assimilation?->tryCapture($event, $ship);
        }
    }

    private function move(BorgSphereEvent $event, Ship $ship): void
    {
        $configuration = $event->getConfiguration();
        if ($event->getPhase() === BorgSpherePhaseEnum::APPROACH) {
            $this->navigation->approach($ship, $configuration);
            if ($ship->getLocation() === $configuration->getColony()->getStarsystemMap()) {
                $event->setPhase(BorgSpherePhaseEnum::ROAMING);
            }
            return;
        }
        if (!$ship->getCondition()->isDestroyed()) {
            $this->navigation->roam($ship, $configuration);
        }
    }

    private function canAct(BorgSphereEvent $event, Ship $ship): bool
    {
        return !$event->isFinished() && $event->getPhase() !== BorgSpherePhaseEnum::REGENERATING
            && !$ship->getCondition()->isDisabled() && !$ship->getCondition()->isDestroyed();
    }
}
