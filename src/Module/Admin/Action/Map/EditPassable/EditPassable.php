<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditPassable;

use Stu\Module\Admin\View\Map\Noop\Noop;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;

final class EditPassable implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_EDIT_PASSABLE';

    private EditPassableRequestInterface $editPassableRequest;

    private MapRepositoryInterface $mapRepository;

    private MapFieldTypeRepositoryInterface $mapFieldTypeRepository;

    public function __construct(
        EditPassableRequestInterface $editPassableRequest,
        MapFieldTypeRepositoryInterface $mapFieldTypeRepository,
        MapRepositoryInterface $mapRepository
    ) {
        $this->editPassableRequest = $editPassableRequest;
        $this->mapFieldTypeRepository = $mapFieldTypeRepository;
        $this->mapRepository = $mapRepository;
    }

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

    public function performSessionCheck(): bool
    {
        return false;
    }
}
