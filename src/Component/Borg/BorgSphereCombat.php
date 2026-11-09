<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\StuTime;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;

final class BorgSphereCombat
{
    /** @var array<int, BorgSphereEvent|null> */
    private array $events = [];

    public function __construct(
        private BorgSphereRepositoryInterface $repository,
        private BorgSphereAttackContext $context,
        private StuTime $time
    ) {}

    public function getEvent(Spacecraft $ship): ?BorgSphereEvent
    {
        $id = $ship->getId();
        if (!array_key_exists($id, $this->events)) {
            $event = $this->repository->getByShip($ship);
            if ($event !== null) {
                $this->repository->lockEvent($event);
            }
            $this->events[$id] = $event;
        }
        $event = $this->events[$id];
        return $event === null || $event->isFinished() ? null : $event;
    }

    public function registerHit(Spacecraft $ship): bool
    {
        $event = $this->getEvent($ship);
        if ($event === null) {
            return false;
        }
        if ($event->getPhase() === BorgSpherePhaseEnum::APPROACH) {
            $event->setPhase(BorgSpherePhaseEnum::ROAMING);
        }
        return true;
    }

    public function blocksDamage(Spacecraft $ship): bool
    {
        return $this->getEvent($ship)?->getPhase() === BorgSpherePhaseEnum::REGENERATING
            && !$this->context->allowsDestruction($ship);
    }

    public function limitHullDamage(Spacecraft $ship, int $damage): int
    {
        $event = $this->getEvent($ship);
        if ($event === null || $this->context->allowsDestruction($ship)) {
            return $damage;
        }
        $minimumHull = max(1, (int) ceil($ship->getMaxHull() / 10));
        if ($ship->getCondition()->getHull() - $damage > $minimumHull) {
            return $damage;
        }
        return max(0, $ship->getCondition()->getHull() - $minimumHull);
    }

    public function finishHit(Spacecraft $ship, InformationInterface $information): void
    {
        $event = $this->getEvent($ship);
        if ($event === null || $event->getPhase() === BorgSpherePhaseEnum::REGENERATING
            || $ship->getCondition()->getHull() > (int) ceil($ship->getMaxHull() / 10)) {
            return;
        }
        foreach ($ship->getSystems() as $system) {
            if ($system->getSystemType()->canBeDamaged() && $this->canDamageSystem($ship, $system)) {
                return;
            }
        }
        $this->disable($event, $ship);
        $information->addInformation('-- Die Sphäre wurde deaktiviert. Der Beschuss wurde eingestellt');
    }

    public function canDamageSystem(Spacecraft $ship, SpacecraftSystem $system): bool
    {
        $event = $this->getEvent($ship);
        if ($event === null) {
            return $system->getStatus() > 0;
        }
        if ($system->getSystemType() !== SpacecraftSystemTypeEnum::TRANSWARP_COIL && $this->context->allowsDestruction($ship)) {
            return $system->getStatus() > 0;
        }
        $minimum = $system->getSystemType() === SpacecraftSystemTypeEnum::TRANSWARP_COIL
            ? max(10, intdiv($event->getTranswarpFloor(), 100)) : 10;
        return $system->getStatus() > $minimum;
    }

    private function disable(BorgSphereEvent $event, Spacecraft $ship): void
    {
        $now = $this->time->time();
        $configuration = $event->getConfiguration();
        if ($event->getFirstDisabledAt() === null) {
            $event->setFirstDisabledAt($now)->setTranswarpFloor($configuration->getSetting('transwarp_initial_bp'));
        }
        $event->setPhase(BorgSpherePhaseEnum::REGENERATING)
            ->setTranswarpProgress($event->getTranswarpFloor())
            ->setNextTickAt($now + $configuration->getSetting('tick_seconds'));
        $ship->getCondition()->setDisabled(true)->setShield((int) ceil($ship->getMaxShield(true) / 10));
        $deactivatedSystems = [SpacecraftSystemTypeEnum::SHIELDS, SpacecraftSystemTypeEnum::PHASER,
            SpacecraftSystemTypeEnum::TORPEDO, SpacecraftSystemTypeEnum::IMPULSEDRIVE,
            SpacecraftSystemTypeEnum::WARPDRIVE, SpacecraftSystemTypeEnum::CLOAK, SpacecraftSystemTypeEnum::TRANSWARP_COIL];
        foreach ($ship->getSystems() as $system) {
            if ($system->getMode()->isActivated() && in_array($system->getSystemType(), $deactivatedSystems, true)) {
                $system->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            }
        }
        $this->repository->save($event);
        $this->context->stopBattleAfterDisabling($ship);
    }

    public function limitSystemStatus(Spacecraft $ship, SpacecraftSystem $system, int $status): ?int
    {
        $event = $this->getEvent($ship);
        if ($event === null) {
            return null;
        }
        if ($system->getSystemType() === SpacecraftSystemTypeEnum::TRANSWARP_COIL) {
            $status = max(intdiv($event->getTranswarpFloor(), 100), $status);
            $event->setTranswarpProgress(max($event->getTranswarpFloor(), $status * 100));
            return max(10, $status);
        }
        return $this->context->allowsDestruction($ship) ? null : max(10, $status);
    }

    public function targetSystems(Spacecraft $ship): ?SpacecraftSystemTypeEnum
    {
        return $this->getEvent($ship) !== null && $this->context->targetsTranswarp($ship)
            ? SpacecraftSystemTypeEnum::TRANSWARP_COIL : null;
    }
}
