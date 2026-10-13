<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use request;
use Stu\Component\Game\RedirectionException;
use Stu\Exception\SessionInvalidException;
use Stu\Lib\Session\AdminImpersonationInterface;
use Stu\Module\Admin\View\Playerlist\Playerlist;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\UserRepositoryInterface;

final class StartImpersonation implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_START_IMPERSONATION';

    public function __construct(
        private readonly AdminImpersonationInterface $impersonation,
        private readonly UserRepositoryInterface $userRepository
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->setView(Playerlist::VIEW_IDENTIFIER);
        if (!request::isPost()) {
            return;
        }

        $target = $this->userRepository->find(request::postIntFatal('uid'));
        if ($target === null) {
            $context->getInfo()->addInformation(_('Spieleraccount nicht gefunden'));
            return;
        }

        $admin = $context->getUser();
        try {
            $this->impersonation->start($admin, $target);
        } catch (SessionInvalidException $e) {
            $context->getInfo()->addInformation($e->getMessage());
            return;
        }

        $context->getGame()->getGameRequest()->setAdminAccess($admin->getId(), $target->getId());

        throw new RedirectionException('/maindesk.php');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
