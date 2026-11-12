<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use Stu\Component\Refactor\RefactorRunner;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;

final class DoRefactor implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_REFACTOR';

    public function __construct(
        private RefactorRunner $refactorRunner,
        private EntityManagerInterface $entityManager
    ) {}

    #[Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(ShowTicks::VIEW_IDENTIFIER);
        $this->entityManager->wrapInTransaction(fn () => $this->refactorRunner->refactor());
        $context->getInfo()->addInformation('Der Refactor wurde durchgeführt');
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
