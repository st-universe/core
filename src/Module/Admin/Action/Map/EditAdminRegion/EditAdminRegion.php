<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditAdminRegion;

use Stu\Module\Admin\View\Map\Noop\Noop;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\MapRegionRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class EditAdminRegion implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_EDIT_ADMIN_REGION';

    private EditAdminRegionRequestInterface $editAdminRegionRequest;

    private MapRegionRepositoryInterface $mapRegionRepository;

    private MapRepositoryInterface $mapRepository;

    public function __construct(
        EditAdminRegionRequestInterface $editAdminRegionRequest,
        MapRegionRepositoryInterface $mapRegionRepository,
        MapRepositoryInterface $mapRepository
    ) {
        $this->editAdminRegionRequest = $editAdminRegionRequest;
        $this->mapRegionRepository = $mapRegionRepository;
        $this->mapRepository = $mapRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $selectedField = $this->mapRepository->find($this->editAdminRegionRequest->getFieldId());

        if ($selectedField === null) {
            return;
        }

        $region = $this->mapRegionRepository->find($this->editAdminRegionRequest->getAdminRegionId());
        if ($region === null) {
            return;
        }

        $selectedField->setAdminRegionId($region->getId());

        $this->mapRepository->save($selectedField);

        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
