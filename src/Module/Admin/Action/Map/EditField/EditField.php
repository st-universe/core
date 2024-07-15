<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditField;

use Override;
use Stu\Module\Admin\View\Map\Noop\Noop;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class EditField implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EDIT_FIELD';

    public function __construct(private EditFieldRequestInterface $editFieldRequest, private MapFieldTypeRepositoryInterface $mapFieldTypeRepository, private MapRepositoryInterface $mapRepository)
    {
    }

    #[Override]
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

        $selectedField->setFieldType($type);

        $this->mapRepository->save($selectedField);

        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
