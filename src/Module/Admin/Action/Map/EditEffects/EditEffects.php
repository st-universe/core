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
use ValueError;

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

        $effectArray = [];
        foreach (request::getArrayFatal('effects') as $value) {
            if (!is_string($value)) {
                continue;
            }

            try {
                $effectArray[] = FieldTypeEffectEnum::from($value);
            } catch (ValueError) {
                return;
            }
        }

        $mode = request::getString('mode');
        $currentEffects = [];
        foreach ($mapFieldType->getEffects() as $effect) {
            $currentEffects[$effect->value] = $effect;
        }

        $selectedEffects = [];
        foreach ($effectArray as $effect) {
            $selectedEffects[$effect->value] = $effect;
        }

        $result = match ($mode) {
            'replace' => $selectedEffects,
            'remove' => array_diff_key($currentEffects, $selectedEffects),
            default => $currentEffects + $selectedEffects
        };

        $mapFieldType->setEffects($result === [] ? null : array_values($result));

        $this->mapFieldTypeRepository->save($mapFieldType);

        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
