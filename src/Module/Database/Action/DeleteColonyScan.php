<?php

declare(strict_types=1);

namespace Stu\Module\Database\Action;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ColonyScanRepositoryInterface;
use Stu\Module\Ship\View\Noop\Noop;

final class DeleteColonyScan implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_COLONY_SCAN';

    public function __construct(
        private ColonyScanRepositoryInterface $colonyScanRepository
    ) {
    }

    public function handle(GameControllerInterface $game): void
    {
        $colonyScan = $this->colonyScanRepository->find(request::getIntFatal('id'));

        if (
            $colonyScan === null
            || $colonyScan->getUser() !== $game->getUser()
        ) {
            return;
        }

        $this->colonyScanRepository->delete($colonyScan);
        $game->setView(Noop::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
