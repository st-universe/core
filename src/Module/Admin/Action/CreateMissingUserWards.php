<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Maintenance\CreateMissingUserAwards;

final class CreateMissingUserWards implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_MISSING_AWARDS';

    public function __construct(
        private CreateMissingUserAwards $createMissingUserAwards
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowScripts::VIEW_IDENTIFIER);

        $this->createMissingUserAwards->handle();

        $game->getInfo()->addInformation('Fehlende User Awards wurden hinzugefügt');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
