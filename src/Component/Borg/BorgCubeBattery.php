<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Lib\Battle\Party\BattlePartyInterface;
use Stu\Module\Spacecraft\Lib\Battle\SpacecraftAttackCauseEnum;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;

final class BorgCubeBattery
{
    /** @var array<int, SpacecraftWrapperInterface> */
    private array $defenders = [];

    public function __construct(private BorgSphereCombat $combat, private StuTime $time) {}

    /** @param callable(): MessageCollectionInterface $callback */
    public function withDefense(BattlePartyInterface $defenders, SpacecraftAttackCauseEnum $cause, callable $callback): MessageCollectionInterface
    {
        $previous = $this->defenders;
        $this->defenders = [];
        try {
            if ($cause === SpacecraftAttackCauseEnum::SHIP_FIGHT) {
                $this->registerDefenders($defenders);
                $this->rechargeDefenders();
            }
            return $callback();
        } finally {
            $this->defenders = $previous;
        }
    }

    public function rechargeDefenders(): void
    {
        foreach ($this->defenders as $wrapper) {
            $this->discharge($wrapper);
        }
    }

    public function discharge(SpacecraftWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();
        if (!isset($this->defenders[$ship->getId()]) || !$this->isAvailable($ship)) {
            return;
        }
        $eps = $wrapper->getEpsSystemData();
        if ($eps === null) {
            return;
        }
        $amount = min($eps->getBattery(), max(0, $eps->getMaxEps() - $eps->getEps()));
        if ($amount < 1) {
            return;
        }
        $eps->setBattery($eps->getBattery() - $amount)
            ->setEps($eps->getEps() + $amount)
            ->setBatteryCooldown($this->time->time() + $amount * 60)
            ->update();
    }

    private function registerDefenders(BattlePartyInterface $defenders): void
    {
        foreach ($defenders->getActiveMembers() as $wrapper) {
            $ship = $wrapper->get();
            if ($ship instanceof Ship && $ship->getUser()->isNpc()
                && $this->combat->getEvent($ship)?->getConfiguration()->isAssimilationEnabled() === true) {
                $this->defenders[$ship->getId()] = $wrapper;
            }
        }
    }

    private function isAvailable(Spacecraft $ship): bool
    {
        if ($ship->getCondition()->isDisabled() || $ship->getCondition()->isDestroyed()
            || !$ship->isSystemHealthy(SpacecraftSystemTypeEnum::EPS)) {
            return false;
        }
        $event = $this->combat->getEvent($ship);
        return $event !== null && $event->getPhase() !== BorgSpherePhaseEnum::REGENERATING;
    }
}
