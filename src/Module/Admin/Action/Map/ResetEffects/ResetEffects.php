<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\ResetEffects;

use Override;
use request;
use Stu\Module\Admin\View\Map\Noop\Noop;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class ResetEffects implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_RESET_EFFECTS';

    public function __construct(
        private MapFieldTypeRepositoryInterface $mapFieldTypeRepository,
        private MapRepositoryInterface $mapRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $selectedField = $this->mapRepository->find(request::getIntFatal('field'));
        if ($selectedField === null) {
            return;
        }

        $mapFieldType = $selectedField->getFieldType();
        $mapFieldType->setEffects(null);

        $this->mapFieldTypeRepository->save($mapFieldType);

        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
