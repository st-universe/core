<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\Action\EditField;

use AccessViolation;
use MapField;
use MapFieldType;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Starmap\View\Noop\Noop;

final class EditField implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_EDIT_FIELD';

    private $editFieldRequest;

    public function __construct(
        EditFieldRequestInterface $editFieldRequest
    ) {
        $this->editFieldRequest = $editFieldRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        if (!$game->isAdmin()) {
            throw new AccessViolation();
        }
        $selectedField = new MapField($this->editFieldRequest->getFieldId());
        $type = new MapFieldType($this->editFieldRequest->getFieldType());
        $selectedField->setFieldId($type->getId());
        $selectedField->save();

        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
