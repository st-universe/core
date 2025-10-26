<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use InvalidArgumentException;
use request;
use Stu\Lib\Pirate\PirateCreationInterface;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class SpawnPirateFleet implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SPAWN_PIRATE_FLEET';

    public function __construct(
        private PirateCreationInterface $pirateCreation,
        private MapRepositoryInterface $mapRepository,
        private LayerRepositoryInterface $layerRepository,
        private ShipRepositoryInterface $shipRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowScripts::VIEW_IDENTIFIER);

        if (!$game->isAdmin()) {
            $game->getInfo()->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $layerId = request::postIntFatal('pirate_layer');
        $cx = request::postIntFatal('pirate_cx');
        $cy = request::postIntFatal('pirate_cy');

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

        if (!$field->getFieldType()->getPassable()) {
            $game->getInfo()->addInformation(sprintf(
                'Die Position %s|%d|%d ist nicht passierbar!',
                $layer->getName(),
                $cx,
                $cy
            ));
            return;
        }

        $fleet = $this->pirateCreation->createPirateFleet();

        foreach ($fleet->getShips() as $ship) {
            $ship->setLocation($field);
            $this->shipRepository->save($ship);
        }

        $game->getInfo()->addInformation(sprintf(
            'Piratenflotte "%s" mit %d Schiffen wurde bei %s|%d|%d gespawnt',
            $fleet->getName(),
            $fleet->getShips()->count(),
            $layer->getName(),
            $cx,
            $cy
        ));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
