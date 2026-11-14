<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\BorgSphereEvent;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;

final class BorgSphereSpawner
{
    public function __construct(
        private BorgSphereRepositoryInterface $repository,
        private BorgSphereNavigation $navigation,
        private ShipCreatorInterface $creator,
        private StuTime $time
    ) {}

    public function spawn(int $configurationId, InformationInterface $information, ?Location $location = null): ?BorgSphereEvent
    {
        $configuration = $this->repository->findConfiguration($configurationId);
        if ($configuration === null || !$configuration->isActive($this->time->time())) {
            $information->addInformation('Für dieses Borg-Schiff ist keine aktive Eventkonfiguration vorhanden');
            return null;
        }
        $this->repository->lockConfiguration($configuration);
        if ($this->repository->getByConfiguration($configuration) !== null) {
            $information->addInformation('Das Borg-Schiff dieser Konfiguration wurde bereits gespawnt');
            return null;
        }
        $location ??= $this->navigation->spawnLocation($configuration);
        if ($location === null) {
            $information->addInformation('Im Zielsystem ist kein sicherer Spawnsektor verfügbar');
            return null;
        }
        $plan = $configuration->getBuildplan();
        $wrapper = $this->creator->createBy($configuration->getUser()->getId(), $plan->getRumpId(), $plan->getId())
            ->setLocation($location)->setSpacecraftName($configuration->getDisplayName())->createCrew()->maxOutSystems()
            ->setAlertState(SpacecraftAlertStateEnum::ALERT_YELLOW)->setTorpedo()->finishConfiguration();
        $ship = $wrapper->get();
        $rpg = $ship->getSystems()->get(SpacecraftSystemTypeEnum::RPG_MODULE->value);
        $rpg?->setMode(SpacecraftSystemModeEnum::MODE_ALWAYS_OFF)->setData(json_encode(['invisible' => false], JSON_THROW_ON_ERROR));
        $this->readyForEvent($wrapper);
        $event = new BorgSphereEvent()->setConfiguration($configuration)->setNextTickAt($this->time->time() + $configuration->getSetting('tick_seconds'))
            ->setPhase($location instanceof Map ? BorgSpherePhaseEnum::ROAMING : BorgSpherePhaseEnum::APPROACH);
        $this->repository->saveSpawn($event, $ship);
        $information->addInformation(sprintf('%s wurde in %s gespawnt', $configuration->getDisplayName(), $location->getSectorString()));
        return $event;
    }
    private function readyForEvent(SpacecraftWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();
        foreach ([SpacecraftSystemTypeEnum::SHIELDS, SpacecraftSystemTypeEnum::PHASER, SpacecraftSystemTypeEnum::TORPEDO, SpacecraftSystemTypeEnum::NBS, SpacecraftSystemTypeEnum::IMPULSEDRIVE] as $type) {
            if ($ship->hasSpacecraftSystem($type) && !$ship->getSystemState($type)) {
                $wrapper->getSpacecraftSystemManager()->activate($wrapper, $type, true);
            }
        }
    }

}
