<?php

declare(strict_types=1);

namespace Stu\Module\Game\Action\Logout;

use Stu\Component\Game\ModuleEnum;
use Stu\Component\Game\RedirectionException;
use Stu\Lib\Session\SessionInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;

/**
 * Performs a logout for the user
 */
final class Logout implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_LOGOUT';

    public function __construct(private SessionInterface $session) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        if ($context->getGame()->hasUser()) {
            $this->session->logout();
        }

        throw new RedirectionException(sprintf('/%s.php', ModuleEnum::INDEX->value));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
