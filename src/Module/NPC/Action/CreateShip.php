<?php

declare(strict_types=1);

namespace Stu\Module\NPC\Action;

use request;
use RuntimeException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipCreatorInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Module\NPC\View\ShowShipCreator\ShowShipCreator;
use Stu\Orm\Repository\NPCLogRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class CreateShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CREATE_SHIP';

    private ShipCreatorInterface $shipCreator;
    private MapRepositoryInterface $mapRepository;
    private ShipBuildplanRepositoryInterface $buildplanRepository;
    private NPCLogRepositoryInterface $npcLogRepository;
    private LayerRepositoryInterface $layerRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct(
        ShipCreatorInterface $shipCreator,
        MapRepositoryInterface $mapRepository,
        ShipBuildplanRepositoryInterface $buildplanRepository,
        NPCLogRepositoryInterface $npcLogRepository,
        LayerRepositoryInterface $layerRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->shipCreator = $shipCreator;
        $this->mapRepository = $mapRepository;
        $this->buildplanRepository = $buildplanRepository;
        $this->npcLogRepository = $npcLogRepository;
        $this->layerRepository = $layerRepository;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShipCreator::VIEW_IDENTIFIER);

        if (!$game->isAdmin() && !$game->isNpc()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin/NPC![/color][/b]'));
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

        if ($reason === '') {
            $game->addInformation("Grund fehlt");
            return;
        }

        $user = $this->userRepository->find($userId);
        if ($user === null) {
            throw new RuntimeException(sprintf('userId %d does not exist', $userId));
        }

        $buildplan = $this->buildplanRepository->find($buildplanId);
        if ($buildplan === null) {
            throw new RuntimeException(sprintf('buildplanId %d does not exist', $buildplanId));
        }

        $layer = $this->layerRepository->find($layerId);
        if ($layer === null) {
            throw new RuntimeException(sprintf('layerId %d does not exist', $layerId));
        }

        $field = $this->mapRepository->getByCoordinates($layer, $cx, $cy);
        if ($field === null) {
            $game->addInformation(sprintf(
                'Die Position %s|%d|%d existiert nicht!',
                $layer->getName(),
                $cx,
                $cy
            ));
            return;
        }

        $moduleNames = [];
        foreach ($buildplan->getModules() as $module) {
            $moduleNames[] = $module->getModule()->getName();
        }

        for ($i = 0; $i < $shipCount; $i++) {
            $creator = $this->shipCreator
                ->createBy($userId, $buildplan->getRump()->getId(), $buildplan->getId())
                ->setLocation($field)
                ->maxOutSystems()
                ->createCrew();

            if ($torpedoTypeId > 0) {
                $creator->setTorpedo($torpedoTypeId);
            }

            $creator->finishConfiguration();
        }

        $logText = sprintf(
            '%s hat für Spieler %s (%d) %dx %s erstellt. Module: %s, Position: %s|%d|%d, Grund: %s',
            $game->getUser()->getName(),
            $user->getName(),
            $userId,
            $shipCount,
            $buildplan->getName(),
            implode(', ', $moduleNames),
            $layer->getName(),
            $cx,
            $cy,
            $reason
        );

        $this->createLogEntry($logText, $game->getUser()->getId());

        $game->addInformation(sprintf('%d Schiff(e) wurden erstellt', $shipCount));
    }

    private function createLogEntry(string $text, int $userId): void
    {
        $entry = $this->npcLogRepository->prototype();
        $entry->setText($text);
        $entry->setSourceUserId($userId);
        $entry->setDate(time());

        $this->npcLogRepository->save($entry);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}