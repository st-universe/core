<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use request;
use Stu\Component\Game\RedirectionException;
use Stu\Lib\Session\AdminImpersonationInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;

final class StopImpersonation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_STOP_IMPERSONATION';

    public function __construct(private readonly AdminImpersonationInterface $impersonation) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        if (!request::isPost()) {
            return;
        }

        $this->impersonation->stop();

        throw new RedirectionException('/admin/?SHOW_PLAYER_LIST=1');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
