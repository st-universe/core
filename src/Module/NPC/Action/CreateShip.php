<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;


use BadMethodCallException;
use InvalidArgumentException;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftFactoryInterface;
use Stu\Module\Station\Lib\Creation\StationCreatorInterface;
use Stu\Orm\Repository\ConstructionProgressModuleRepositoryInterface;
use Stu\Orm\Repository\ConstructionProgressRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;
use Stu\Module\NPC\View\ShowShipCreator\ShowShipCreator;
use Stu\Orm\Repository\NPCLogRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Module;
use Stu\Orm\Repository\ModuleRepositoryInterface;

final class CreateShip implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_CREATE_SHIP';

    public function __construct(
        private ShipCreatorInterface $shipCreator,
        private StationCreatorInterface $stationCreator,
        private SpacecraftFactoryInterface $spacecraftFactory,
        private MapRepositoryInterface $mapRepository,
        private SpacecraftBuildplanRepositoryInterface $buildplanRepository,
        private NPCLogRepositoryInterface $npcLogRepository,
        private LayerRepositoryInterface $layerRepository,
        private UserRepositoryInterface $userRepository,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private ConstructionProgressRepositoryInterface $constructionProgressRepository,
        private ConstructionProgressModuleRepositoryInterface $constructionProgressModuleRepository,
        private ModuleRepositoryInterface $moduleRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShipCreator::VIEW_IDENTIFIER);

        if (!$game->isAdmin() && !$game->isNpc()) {
            $game->getInfo()->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin/NPC![/color][/b]'));
            return;
        }

        $userId = request::postIntFatal('userId');
        $buildplanId = request::postIntFatal('buildplanId');
        $shipCount = request::postIntFatal('shipcount');
        $layerId = request::postIntFatal('layer');
        $cx = request::postIntFatal('cx');
        $cy = request::postIntFatal('cy');
        $reason = request::postString('reason');
        $torpedoTypeId = request::postInt('torpedoTypeId');
        $crewAmount = request::postInt('crew_input');
        $underConstruction = request::postInt('underConstruction') === 1;

        if ($game->getUser()->isNpc() && $reason === '') {
            $game->getInfo()->addInformation("Grund fehlt");
            return;
        }

        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new InvalidArgumentException(sprintf('userId %d does not exist', $userId));
        }

        $buildplan = $this->buildplanRepository->find($buildplanId);
        if ($buildplan === null) {
            throw new InvalidArgumentException(sprintf('buildplanId %d does not exist', $buildplanId));
        }

        $layer = $this->layerRepository->find($layerId);
        if ($layer === null) {
            throw new InvalidArgumentException(sprintf('layerId %d does not exist', $layerId));
        }

        $field = $this->mapRepository->getByCoordinates($layer, $cx, $cy);
        if ($field === null) {
            $game->getInfo()->addInformation(sprintf(
                'Die Position %s|%d|%d existiert nicht!',
                $layer->getName(),
                $cx,
                $cy
            ));
            return;
        }

        $moduleNames = [];
        foreach ($buildplan->getModulesOrdered() as $module) {
            $moduleNames[] = $module->getModule()->getName();
        }

        $wantedSpecialModules = [];
        if ($buildplan->getRump()->isStation()) {
            $wantedSpecialModuleIds = request::postArray('mod_9');
            foreach ($wantedSpecialModuleIds as $wantedModId) {
                $mod = $this->moduleRepository->find((int)$wantedModId);
                if ($mod !== null) {
                    $wantedSpecialModules[] = $mod;
                }
            }
        }

        if ($buildplan->getRump()->isStation() && $underConstruction) {
            $this->createStationsUnderConstruction($userId, $buildplan, $field, $shipCount, true, $wantedSpecialModules);
        } elseif ($buildplan->getRump()->isStation()) {
            $this->createStationsUnderConstruction($userId, $buildplan, $field, $shipCount, false, $wantedSpecialModules);
        } else {
            for ($i = 0; $i < $shipCount; $i++) {
                $creator = $this->shipCreator
                    ->createBy($userId, $buildplan->getRump()->getId(), $buildplan->getId())
                    ->setLocation($field)
                    ->maxOutSystems()
                    ->createCrew($crewAmount);

                if ($torpedoTypeId > 0) {
                    $creator->setTorpedo($torpedoTypeId);
                }

                $creator->finishConfiguration();
            }
        }

        $entityType = ($buildplan->getRump()->isStation() && $underConstruction) ? 'Station(en) im Bau' : 'Schiff(e)';

        $logText = sprintf(
            '%s hat für Spieler %s (%d) %dx %s erstellt. Module: %s, Position: %s (%d|%d), Crew: %d%s Grund: %s',
            $game->getUser()->getName(),
            $user->getName(),
            $userId,
            $shipCount,
            $buildplan->getName(),
            implode(', ', $moduleNames),
            $layer->getName(),
            $cx,
            $cy,
            $crewAmount,
            ($buildplan->getRump()->isStation() && $underConstruction) ? ', Im Bau: Ja' : '',
            $reason
        );
        if ($game->getUser()->isNpc()) {
            $this->createLogEntry($logText, $game->getUser()->getId());
        }

        $game->getInfo()->addInformation(sprintf('%d %s wurden erstellt', $shipCount, $entityType));
    }

    /**
     * @param array<Module> $wantedSpecialModules
     */
    private function createStationsUnderConstruction(int $userId, SpacecraftBuildplan $buildplan, Location $field, int $stationCount, bool $inConstruction, array $wantedSpecialModules): void
    {
        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new InvalidArgumentException(sprintf('User with id %d not found', $userId));
        }

        for ($i = 0; $i < $stationCount; $i++) {
            if ($inConstruction) {
                $this->createStationUnderConstruction($user->getId(), $buildplan, $field, $wantedSpecialModules);
            } else {
                $this->createFinishedStation($user->getId(), $buildplan, $field, $wantedSpecialModules);
            }
        }
    }

    /**
     * @param array<Module> $wantedSpecialModules
     */
    private function createStationUnderConstruction(int $userId, SpacecraftBuildplan $buildplan, Location $field, array $wantedSpecialModules): void
    {
        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new InvalidArgumentException(sprintf('User with id %d not found', $userId));
        }

        $rump = $buildplan->getRump();
        $station = $this->spacecraftFactory->create($rump);
        if (!$station instanceof Station) {
            throw new BadMethodCallException(sprintf('Rump %d is not a station', $rump->getId()));
        }

        $baseHull = $rump->getBaseValues()->getBaseHull();

        $station->setUser($user);
        $station->setRump($rump);
        $station->setBuildplan($buildplan);
        $station->setName(sprintf('%s in Bau', $rump->getName()));
        $station->setMaxHuell($baseHull);
        $station->getCondition()->setHull(intdiv($baseHull, 2));
        $station->getCondition()->setState(SpacecraftStateEnum::UNDER_CONSTRUCTION);
        $station->setLocation($field);

        $this->spacecraftRepository->save($station);

        $progress = $station->getConstructionProgress() ?? $this->constructionProgressRepository->prototype();
        $progress->setStation($station);
        $progress->setRemainingTicks($rump->getBuildtime());

        $this->constructionProgressRepository->save($progress);

        foreach ($buildplan->getModules() as $buildplanModule) {
            $progressModule = $this->constructionProgressModuleRepository->prototype();
            $progressModule->setConstructionProgress($progress);
            $progressModule->setModule($buildplanModule->getModule());

            $this->constructionProgressModuleRepository->save($progressModule);
        }

        foreach ($wantedSpecialModules as $mod) {
            $progressModule = $this->constructionProgressModuleRepository->prototype();
            $progressModule->setConstructionProgress($progress);
            $progressModule->setModule($mod);

            $this->constructionProgressModuleRepository->save($progressModule);
        }
    }

    /**
     * @param array<Module> $wantedSpecialModules
     */
    private function createFinishedStation(int $userId, SpacecraftBuildplan $buildplan, Location $field, array $wantedSpecialModules): void
    {
        $rump = $buildplan->getRump();
        $station = $this->spacecraftFactory->create($rump);
        if (!$station instanceof Station) {
            throw new BadMethodCallException(sprintf('Rump %d is not a station', $rump->getId()));
        }

        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new InvalidArgumentException(sprintf('User with id %d not found', $userId));
        }

        $baseHull = $rump->getBaseValues()->getBaseHull();

        $station->setUser($user);
        $station->setRump($rump);
        $station->setBuildplan($buildplan);
        $station->setName($rump->getName());
        $station->setMaxHuell($baseHull);
        $station->getCondition()->setHull($baseHull);
        $station->getCondition()->setState(SpacecraftStateEnum::NONE);
        $station->setLocation($field);

        $this->spacecraftRepository->save($station);

        $progress = $this->constructionProgressRepository->prototype();
        $progress->setStation($station);
        $progress->setRemainingTicks(0);

        $this->constructionProgressRepository->save($progress);

        foreach ($buildplan->getModules() as $buildplanModule) {
            $progressModule = $this->constructionProgressModuleRepository->prototype();
            $progressModule->setConstructionProgress($progress);
            $progressModule->setModule($buildplanModule->getModule());

            $this->constructionProgressModuleRepository->save($progressModule);
        }

        foreach ($wantedSpecialModules as $mod) {
            $progressModule = $this->constructionProgressModuleRepository->prototype();
            $progressModule->setConstructionProgress($progress);
            $progressModule->setModule($mod);

            $this->constructionProgressModuleRepository->save($progressModule);
        }

        $this->stationCreator
            ->createBy($userId, $rump->getId(), $buildplan->getId(), $progress)
            ->finishConfiguration();
    }

    private function createLogEntry(string $text, int $userId): void
    {
        $entry = $this->npcLogRepository->prototype();
        $entry->setText($text);
        $entry->setSourceUserId($userId);
        $entry->setDate(time());

        $this->npcLogRepository->save($entry);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
