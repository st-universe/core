<?php

declare(strict_types=1);

namespace Stu\Module\Database\Action;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Spacecraft\View\Noop\Noop;
use Stu\Orm\Repository\ColonyScanRepositoryInterface;

final class DeleteColonyScan implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_COLONY_SCAN';

    public function __construct(
        private ColonyScanRepositoryInterface $colonyScanRepository
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $colonyScan = $this->colonyScanRepository->find(request::getIntFatal('id'));

        if (
            $colonyScan === null
            || $colonyScan->getUser()->getId() !== $context->getUser()->getId()
        ) {
            return;
        }

        $this->colonyScanRepository->delete($colonyScan);
        $context->setView(Noop::VIEW_IDENTIFIER);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
