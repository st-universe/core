<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditPassable;

use Stu\Module\Admin\View\Map\Noop\Noop;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class EditPassable implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EDIT_PASSABLE';

    public function __construct(private EditPassableRequestInterface $editPassableRequest, private MapFieldTypeRepositoryInterface $mapFieldTypeRepository, private MapRepositoryInterface $mapRepository)
    {
    }

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $selectedField = $this->mapRepository->find($this->editPassableRequest->getFieldId());

        if ($selectedField === null) {
            return;
        }

        $selectedMapFType = $selectedField->getFieldType();

        $passable = $this->editPassableRequest->getPassable();

        if ($passable === 1) {
            $selectedMapFType->setPassable(true);
        }

        if ($passable === 2) {
            $selectedMapFType->setPassable(false);
        }

        $this->mapFieldTypeRepository->save($selectedMapFType);

        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
