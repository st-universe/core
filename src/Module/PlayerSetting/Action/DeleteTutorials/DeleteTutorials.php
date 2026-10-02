<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\DeleteTutorials;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Orm\Repository\UserTutorialRepositoryInterface;

final class DeleteTutorials implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_TUTORIALS';

    public function __construct(private UserTutorialRepositoryInterface $userTutorialRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $user = $context->getUser();

        $this->userTutorialRepository->truncateByUser($user);


        $context->getInfo()->addInformation(_('Tutorial wurden deaktiviert'));
    }


    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
