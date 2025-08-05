<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\DeleteTutorials;

use Override;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\UserTutorialRepositoryInterface;

final class DeleteTutorials implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_TUTORIALS';

    public function __construct(private UserTutorialRepositoryInterface $userTutorialRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $this->userTutorialRepository->truncateByUser($user);


        $game->getInfo()->addInformation(_('Tutorial wurden deaktiviert'));
    }


    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
