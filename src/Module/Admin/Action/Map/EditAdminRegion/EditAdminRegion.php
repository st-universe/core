<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditAdminRegion;

use Stu\Module\Admin\View\Map\Noop\Noop;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\MapRegionRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class EditAdminRegion implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EDIT_ADMIN_REGION';

    public function __construct(private EditAdminRegionRequestInterface $editAdminRegionRequest, private MapRegionRepositoryInterface $mapRegionRepository, private MapRepositoryInterface $mapRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
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

        $context->setView(Noop::VIEW_IDENTIFIER);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
