<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Action\EditField;

use AccessViolation;
use MapField;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Starmap\View\Noop\Noop;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;

final class EditField implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_EDIT_FIELD';

    private $editFieldRequest;

    private $mapFieldTypeRepository;

    public function __construct(
        EditFieldRequestInterface $editFieldRequest,
        MapFieldTypeRepositoryInterface $mapFieldTypeRepository
    ) {
        $this->editFieldRequest = $editFieldRequest;
        $this->mapFieldTypeRepository = $mapFieldTypeRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        if (!$game->isAdmin()) {
            throw new AccessViolation();
        }
        $selectedField = new MapField($this->editFieldRequest->getFieldId());
        $type = $this->mapFieldTypeRepository->find($this->editFieldRequest->getFieldType());
        $selectedField->setFieldId($type->getId());
        $selectedField->save();

        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
