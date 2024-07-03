<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditSystemType;

use Override;
use Stu\Module\Admin\View\Map\Noop\Noop;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\StarSystemTypeRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class EditSystemType implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EDIT_SYSTEMTYPE_FIELD';

    public function __construct(private EditSystemTypeRequestInterface $editSystemTypeRequest, private StarSystemTypeRepositoryInterface $starSystemTypeRepository, private MapRepositoryInterface $mapRepository)
    {
    }

    #[Override]
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

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
