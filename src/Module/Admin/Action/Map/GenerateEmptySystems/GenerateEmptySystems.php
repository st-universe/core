<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\GenerateEmptySystems;

use request;
use Stu\Component\StarSystem\StarSystemCreationInterface;
use Stu\Module\Admin\View\Map\ShowMapEditor;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class GenerateEmptySystems implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'GENERATE_EMPTY_SYSTEMS';

    private LayerRepositoryInterface $layerRepository;

    private MapRepositoryInterface $mapRepository;

    private StarSystemCreationInterface $starSystemCreation;

    public function __construct(
        LayerRepositoryInterface $layerRepository,
        MapRepositoryInterface $mapRepository,
        StarSystemCreationInterface $starSystemCreation
    ) {
        $this->layerRepository = $layerRepository;
        $this->mapRepository = $mapRepository;
        $this->starSystemCreation = $starSystemCreation;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowMapEditor::VIEW_IDENTIFIER);

        //LAYER
        $layers = $this->layerRepository->findAllIndexed();

        $layerId = request::getInt('layerid');
        $layer = $layerId === 0 ? current($layers) : $layers[$layerId];

        if ($layer === false) {
            return;
        }

        $mapArray = $this->mapRepository->getWithEmptySystem($layer);

        foreach ($mapArray as $map) {
            $this->starSystemCreation->recreateStarSystem($map);
        }

        $game->addInformation(sprintf('Es wurden %d Systeme generiert.', count($mapArray)));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
