<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Action\EditSystemField;

use AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Starmap\View\Noop\Noop;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;
use SystemMap;

final class EditSystemField implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_EDIT_SYSTEM_FIELD';

    private $editSystemFieldRequest;

    private $mapFieldTypeRepository;

    public function __construct(
        EditSystemFieldRequestInterface $editSystemFieldRequest,
        MapFieldTypeRepositoryInterface $mapFieldTypeRepository
    ) {
        $this->editSystemFieldRequest = $editSystemFieldRequest;
        $this->mapFieldTypeRepository = $mapFieldTypeRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        if (!$game->isAdmin()) {
            throw new AccessViolation();
        }
        $selectedField = new SystemMap($this->editSystemFieldRequest->getFieldId());
        $type = $this->mapFieldTypeRepository->find($this->editSystemFieldRequest->getFieldType());
        $selectedField->setFieldId($type->getId());
        $selectedField->save();

        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
