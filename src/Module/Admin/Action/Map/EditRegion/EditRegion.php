<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditRegion;

use Override;
use Stu\Module\Admin\View\Map\Noop\Noop;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\MapRegionRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class EditRegion implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EDIT_REGION';

    public function __construct(private EditRegionRequestInterface $editRegionRequest, private MapRegionRepositoryInterface $mapRegionRepository, private MapRepositoryInterface $mapRepository)
    {
    }

    #[Override]
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

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
