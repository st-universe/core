<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Player\Relation\PlayerRelationDeterminatorInterface;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Control\StuTime;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\Fleet\LeaveFleetInterface;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftAssimilated;
use Stu\Orm\Repository\BorgAssimilationRepositoryInterface;

final class BorgAssimilationCapture
{
    public function __construct(
        private BorgAssimilationRepositoryInterface $repository,
        private PlayerRelationDeterminatorInterface $relations,
        private LeaveFleetInterface $fleets,
        private PrivateMessageSenderInterface $messages,
        private StuRandom $random,
        private StuTime $time
    ) {}

    public function tryCapture(BorgSphereEvent $event, Ship $source): bool
    {
        if (!$this->canCapture($event, $source)) {
            return false;
        }
        $this->repository->lock($source);
        $targets = $this->targets($source);
        if ($targets === [] || $this->random->rand(1, 100) > ($event->getConfiguration()->getSettings()['assimilation_chance_percent'] ?? 33)) {
            return false;
        }
        $target = $targets[$this->random->array_rand($targets)];
        return $this->captureSelected($event, $source, $target);
    }

    public function hasTargets(BorgSphereEvent $event, Ship $source): bool
    {
        return $this->canCapture($event, $source) && $this->targets($source) !== [];
    }

    public function captureTarget(BorgSphereEvent $event, Ship $source, Ship $target): bool
    {
        if (!$this->canCaptureTarget($event, $source, $target)) {
            return false;
        }
        $this->repository->lock($source);
        return $this->captureSelected($event, $source, $target);
    }

    public function canCaptureTarget(BorgSphereEvent $event, Ship $source, Ship $target): bool
    {
        return $this->canCapture($event, $source) && $this->isTarget($source, $target);
    }

    private function captureSelected(BorgSphereEvent $event, Ship $source, Ship $target): bool
    {
        $this->repository->lock($target);
        if (!$this->isTarget($source, $target)) {
            return false;
        }
        $this->capture($event, $source, $target);
        return true;
    }

    /** @return list<Ship> */
    private function targets(Ship $source): array
    {
        return array_values(array_filter($source->getLocation()->getSpacecraftsWithoutVacation()->toArray(), fn ($target): bool => $target instanceof Ship && $this->isTarget($source, $target)));
    }

    private function canCapture(BorgSphereEvent $event, Ship $source): bool
    {
        return ($event->getConfiguration()->getSettings()['assimilation_enabled'] ?? 0) === 1
            && !$event->isFinished() && $event->getPhase() !== BorgSpherePhaseEnum::REGENERATING
            && $this->isSourceAvailable($source) && !$source->isTractoring()
            && $source->isSystemHealthy(SpacecraftSystemTypeEnum::TRACTOR_BEAM);
    }

    private function isSourceAvailable(Ship $source): bool
    {
        return !$source->getCondition()->isDisabled() && !$source->getCondition()->isDestroyed()
            && !$source->isWarped() && !$source->isCloaked()
            && !($source->getHoldingWeb()?->isFinished() ?? false);
    }

    private function isTarget(Ship $source, Ship $target): bool
    {
        return $target !== $source && $target->getLocation() === $source->getLocation()
            && $target->getUser()->getId() !== $source->getUser()->getId()
            && !$target->isWarped() && !$target->isCloaked() && !$target->isRpgModuleInvisible()
            && $this->isAvailable($target)
            && $this->isAllowedRump($target)
            && !$this->relations->isFriend($source->getUser(), $target->getUser());
    }

    private function isAllowedRump(Ship $target): bool
    {
        return in_array($target->getRump()->getCategoryId(), [SpacecraftRumpCategoryEnum::CRUISER, SpacecraftRumpCategoryEnum::DESTROYER, SpacecraftRumpCategoryEnum::ESCORT], true)
            && $this->repository->isAssimilateable($target->getRump());
    }

    private function isAvailable(Ship $target): bool
    {
        return !$target->getCondition()->isDestroyed() && !$target->isBeingAssimilated()
            && $target->getTractoringSpacecraft() === null
            && !$target->getUser()->isVacationRequestOldEnough()
            && !($target->getHoldingWeb()?->isFinished() ?? false);
    }

    private function capture(BorgSphereEvent $event, Ship $source, Ship $target): void
    {
        $settings = $event->getConfiguration()->getSettings();
        $this->fleets->leaveFleet($target);
        $target->setDockedTo(null);
        $source->getSpacecraftSystem(SpacecraftSystemTypeEnum::TRACTOR_BEAM)->setMode(SpacecraftSystemModeEnum::MODE_ON);
        $source->setTractoredShip($target);
        $entry = $target->getAssimilation() ?? new SpacecraftAssimilated()->setSpacecraft($target);
        $entry->start($source, $this->time->time(), $settings['assimilation_duration_seconds'] ?? 900)
            ->setNaniteCommodityId($event->getConfiguration()->getSetting('nanite_commodity_id'))
            ->setNaniteReward($settings['assimilation_nanite_min'] ?? 1, $settings['assimilation_nanite_max'] ?? 30);
        $this->repository->save($entry);
        $this->messages->send($source->getUser()->getId(), $target->getUser()->getId(), sprintf('Die %s wurde vom Traktorstrahl der %s erfasst. Borg-Drohnen beginnen mit der Assimilation. Das Schiff muss innerhalb von 15 Minuten befreit werden', $target->getName(), $source->getName()), PrivateMessageFolderTypeEnum::SPECIAL_SHIP, $target);
    }
}
