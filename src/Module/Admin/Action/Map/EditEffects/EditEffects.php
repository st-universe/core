<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\EditEffects;

use request;
use Stu\Lib\Map\FieldTypeEffectEnum;
use Stu\Module\Admin\View\Map\Noop\Noop;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class EditEffects implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EDIT_EFFECTS';

    public function __construct(
        private MapFieldTypeRepositoryInterface $mapFieldTypeRepository,
        private MapRepositoryInterface $mapRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $selectedField = $this->mapRepository->find(request::getIntFatal('field'));
        if ($selectedField === null) {
            return;
        }

        $mapFieldType = $selectedField->getFieldType();

        $effectArray = array_map(
            fn(string $value): FieldTypeEffectEnum => FieldTypeEffectEnum::from($value),
            request::getArrayFatal('effects')
        );

        $mapFieldType->setEffects($effectArray);

        $this->mapFieldTypeRepository->save($mapFieldType);

        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
