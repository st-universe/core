<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Action\EditField;

use AccessViolation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Starmap\View\Noop\Noop;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class EditField implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_EDIT_FIELD';

    private $editFieldRequest;

    private $mapFieldTypeRepository;

    private $mapRepository;

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
        if (!$game->isAdmin()) {
            throw new AccessViolation();
        }
        $selectedField = $this->mapRepository->find($this->editFieldRequest->getFieldId());

        if ($selectedField === null) {
            return;
        }

        $type = $this->mapFieldTypeRepository->find($this->editFieldRequest->getFieldType());
        $selectedField->setFieldId($type->getId());

        $this->mapRepository->save($selectedField);

        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
