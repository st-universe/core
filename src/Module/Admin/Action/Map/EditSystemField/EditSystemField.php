<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditSystemField;

use Stu\Module\Admin\View\Map\Noop\Noop;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class EditSystemField implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_EDIT_SYSTEM_FIELD';

    private EditSystemFieldRequestInterface $editSystemFieldRequest;

    private MapFieldTypeRepositoryInterface $mapFieldTypeRepository;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    public function __construct(
        EditSystemFieldRequestInterface $editSystemFieldRequest,
        MapFieldTypeRepositoryInterface $mapFieldTypeRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository
    ) {
        $this->editSystemFieldRequest = $editSystemFieldRequest;
        $this->mapFieldTypeRepository = $mapFieldTypeRepository;
        $this->starSystemMapRepository = $starSystemMapRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        /** @var StarSystemMapInterface $selectedField */
        $selectedField = $this->starSystemMapRepository->find($this->editSystemFieldRequest->getFieldId());
        $type = $this->mapFieldTypeRepository->find($this->editSystemFieldRequest->getFieldType());
        $selectedField->setFieldId($type->getId());

        $this->starSystemMapRepository->save($selectedField);

        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
