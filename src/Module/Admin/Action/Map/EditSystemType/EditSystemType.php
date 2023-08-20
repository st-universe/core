<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditSystemType;

use Stu\Module\Admin\View\Map\Noop\Noop;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\StarSystemTypeRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class EditSystemType implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_EDIT_SYSTEMTYPE_FIELD';

    private EditSystemTypeRequestInterface $editSystemTypeRequest;

    private StarSystemTypeRepositoryInterface $starSystemTypeRepository;

    private MapRepositoryInterface $mapRepository;

    public function __construct(
        EditSystemTypeRequestInterface $editSystemTypeRequest,
        StarSystemTypeRepositoryInterface $starSystemTypeRepository,
        MapRepositoryInterface $mapRepository
    ) {
        $this->editSystemTypeRequest = $editSystemTypeRequest;
        $this->starSystemTypeRepository = $starSystemTypeRepository;
        $this->mapRepository = $mapRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $selectedField = $this->mapRepository->find($this->editSystemTypeRequest->getFieldId());

        if ($selectedField === null) {
            return;
        }

        $type = $this->starSystemTypeRepository->find($this->editSystemTypeRequest->getSystemType());
        if ($type === null) {
            return;
        }

        $selectedField->setSystemTypeId($type->getId());

        $this->mapRepository->save($selectedField);

        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
