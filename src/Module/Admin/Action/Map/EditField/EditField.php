<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditField;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Admin\View\Map\Noop\Noop;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class EditField implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_EDIT_FIELD';

    private EditFieldRequestInterface $editFieldRequest;

    private MapFieldTypeRepositoryInterface $mapFieldTypeRepository;

    private MapRepositoryInterface $mapRepository;

    public function __construct(
        EditFieldRequestInterface $editFieldRequest,
        MapFieldTypeRepositoryInterface $mapFieldTypeRepository,
        MapRepositoryInterface $mapRepository
    ) {
        $this->editFieldRequest = $editFieldRequest;
        $this->mapFieldTypeRepository = $mapFieldTypeRepository;
        $this->mapRepository = $mapRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $selectedField = $this->mapRepository->find($this->editFieldRequest->getFieldId());

        if ($selectedField === null) {
            return;
        }

        $type = $this->mapFieldTypeRepository->find($this->editFieldRequest->getFieldType());
        if ($type === null) {
            return;
        }
        
        $selectedField->setFieldId($type->getId());

        $this->mapRepository->save($selectedField);

        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
