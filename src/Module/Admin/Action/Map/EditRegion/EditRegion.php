<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditRegion;

use Stu\Module\Admin\View\Map\Noop\Noop;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\MapRegionRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class EditRegion implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_EDIT_REGION';

    private EditRegionRequestInterface $editRegionRequest;

    private MapRegionRepositoryInterface $mapRegionRepository;

    private MapRepositoryInterface $mapRepository;

    public function __construct(
        EditRegionRequestInterface $editRegionRequest,
        MapRegionRepositoryInterface $mapRegionRepository,
        MapRepositoryInterface $mapRepository
    ) {
        $this->editRegionRequest = $editRegionRequest;
        $this->mapRegionRepository = $mapRegionRepository;
        $this->mapRepository = $mapRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $selectedField = $this->mapRepository->find($this->editRegionRequest->getFieldId());

        if ($selectedField === null) {
            return;
        }

        $region = $this->mapRegionRepository->find($this->editRegionRequest->getRegionId());
        if ($region === null) {
            return;
        }

        $selectedField->setRegionId($region->getId());

        $this->mapRepository->save($selectedField);

        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
