<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use InvalidArgumentException;
use request;
use Stu\Lib\Pirate\PirateCreationInterface;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Map;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class SpawnPirateFleet implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SPAWN_PIRATE_FLEET';

    public function __construct(
        private PirateCreationInterface $pirateCreation,
        private MapRepositoryInterface $mapRepository,
        private LayerRepositoryInterface $layerRepository
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowScripts::VIEW_IDENTIFIER);

        $layerId = request::postIntFatal('pirate_layer');
        $layer = $this->layerRepository->find($layerId);
        if ($layer === null) {
            throw new InvalidArgumentException(sprintf('layerId %d does not exist', $layerId));
        }

        $field = $this->getSpawnLocation($layer, $context);
        if ($field === null) {
            return;
        }

        $pirateSetupId = request::postInt('pirate_setup_id');
        $pirateSetupId = $pirateSetupId > 0 ? $pirateSetupId : null;

        $fleet = $this->pirateCreation->createPirateFleet(null, $pirateSetupId, $field);

        $context->getInfo()->addInformation(sprintf(
            'Piratenflotte "%s" mit %d Schiffen wurde bei %s|%d|%d gespawnt',
            $fleet->getName(),
            $fleet->getShips()->count(),
            $layer->getName(),
            $field->getX(),
            $field->getY()
        ));
    }

    private function getSpawnLocation(Layer $layer, ActionControllerContext $context): ?Map
    {
        $cx = trim((string) request::postString('pirate_cx'));
        $cy = trim((string) request::postString('pirate_cy'));

        if ($cx === '' && $cy === '') {
            $field = $this->mapRepository->getRandomPirateSpawnLocation($layer);
            if ($field === null) {
                $context->getInfo()->addInformation(sprintf(
                    'Auf Layer %s können keine Piraten gespawnt werden, da keine zulässige Spawnposition vorhanden ist',
                    $layer->getName()
                ));
            }
            return $field;
        }

        if ($cx === '' || $cy === '') {
            $context->getInfo()->addInformation('Bitte beide Koordinaten angeben oder beide für eine zufällige Spawnposition leer lassen');
            return null;
        }

        $cx = request::postIntFatal('pirate_cx');
        $cy = request::postIntFatal('pirate_cy');
        $field = $this->mapRepository->getByCoordinates($layer, $cx, $cy);
        if ($field === null) {
            $context->getInfo()->addInformation(sprintf(
                'Die Position %s|%d|%d existiert nicht!',
                $layer->getName(),
                $cx,
                $cy
            ));
            return null;
        }

        if (!$field->getFieldType()->getPassable()) {
            $context->getInfo()->addInformation(sprintf(
                'Die Position %s|%d|%d ist nicht passierbar!',
                $layer->getName(),
                $cx,
                $cy
            ));
            return null;
        }

        return $field;
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
