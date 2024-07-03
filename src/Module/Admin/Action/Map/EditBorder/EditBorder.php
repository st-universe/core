<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditBorder;

use Override;
use Stu\Module\Admin\View\Map\Noop\Noop;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\MapBorderTypeRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class EditBorder implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EDIT_BORDER';

    public function __construct(private EditBorderRequestInterface $editBorderRequest, private MapBorderTypeRepositoryInterface $mapBorderTypeRepository, private MapRepositoryInterface $mapRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $selectedField = $this->mapRepository->find($this->editBorderRequest->getFieldId());

        if ($selectedField === null) {
            return;
        }

        $border = $this->mapBorderTypeRepository->find($this->editBorderRequest->getBorder());
        if ($border === null) {
            return;
        }

        $selectedField->setBorderTypeId($border->getId());

        $this->mapRepository->save($selectedField);

        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
