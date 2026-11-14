<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;

final class BorgTractorRescueParticipants
{
    public function __construct(private SpacecraftWrapperFactoryInterface $wrappers, private TholianWebUtilInterface $webs) {}

    /** @return list<SpacecraftWrapperInterface> */
    public function getParticipants(SpacecraftWrapperInterface $source, Ship $cube): array
    {
        $ship = $source->get();
        if (!$ship instanceof Ship) {
            return [];
        }
        $participants = [];
        $seen = [];
        foreach ([$ship, ...($ship->getFleet()?->getShips()->toArray() ?? [])] as $candidate) {
            if (isset($seen[$candidate->getId()]) || !$this->isCandidate($candidate, $ship, $cube)) {
                continue;
            }
            $seen[$candidate->getId()] = true;
            $wrapper = $candidate === $ship ? $source : $this->wrappers->wrapShip($candidate);
            if ($this->canFire($wrapper)) {
                $participants[] = $wrapper;
            }
        }
        return $participants;
    }

    private function isCandidate(Ship $candidate, Ship $source, Ship $cube): bool
    {
        return $candidate->getLocation() === $cube->getLocation()
            && $candidate->getUser()->getId() === $source->getUser()->getId()
            && $this->isAvailable($candidate)
            && !$this->webs->isTargetOutsideFinishedTholianWeb($candidate, $cube)
            && !$this->webs->isTargetInsideFinishedTholianWeb($candidate, $cube);
    }

    private function isAvailable(Ship $ship): bool
    {
        return !$ship->isWarped() && !$ship->isCloaked() && !$ship->isRpgModuleInvisible()
            && !$ship->getCondition()->isDestroyed() && !$ship->getCondition()->isDisabled()
            && !$ship->isBeingAssimilated() && !$ship->getRump()->isEscapePods() && $ship->hasEnoughCrew();
    }

    private function canFire(SpacecraftWrapperInterface $wrapper): bool
    {
        $ship = $wrapper->get();
        return $wrapper->canFire() && $ship->getPhaserState() && $ship->getRump()->getPhaserVolleys() > 0
            && ($wrapper->getEpsSystemData()?->getEps() ?? 0) > 0;
    }
}
