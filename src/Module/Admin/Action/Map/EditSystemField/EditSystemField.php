<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditSystemField;

use Override;
use RuntimeException;
use Stu\Module\Admin\View\Map\Noop\Noop;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class EditSystemField implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EDIT_SYSTEM_FIELD';

    public function __construct(private EditSystemFieldRequestInterface $editSystemFieldRequest, private MapFieldTypeRepositoryInterface $mapFieldTypeRepository, private StarSystemMapRepositoryInterface $starSystemMapRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        /** @var StarSystemMapInterface $selectedField */
        $selectedField = $this->starSystemMapRepository->find($this->editSystemFieldRequest->getFieldId());
        $fieldTypeId = $this->editSystemFieldRequest->getFieldType();
        $type = $this->mapFieldTypeRepository->find($fieldTypeId);

        if ($type === null) {
            throw new RuntimeException(sprintf('fieldType with id %d does not exist', $fieldTypeId));
        }
        $selectedField->setFieldType($type);

        $this->starSystemMapRepository->save($selectedField);

        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
