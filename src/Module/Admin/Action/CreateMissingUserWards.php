<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Override;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Award\Lib\CreateUserAwardInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\DatabaseCategoryInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\DatabaseCategoryRepositoryInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class CreateMissingUserWards implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_MISSING_AWARDS';

    public function __construct(private UserRepositoryInterface $userRepository, private DatabaseCategoryRepositoryInterface $databaseCategoryRepository, private DatabaseUserRepositoryInterface $databaseUserRepository, private CreateUserAwardInterface $createUserAward)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowScripts::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->isAdmin()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $categories = $this->databaseCategoryRepository->findAll();

        foreach ($this->userRepository->getNonNpcList() as $user) {
            foreach ($categories as $category) {
                $this->checkForMissingAward($user, $category);
            }
        }

        $game->addInformation(_('Fehlende User Awards wurden hinzugefügt'));
    }

    private function checkForMissingAward(UserInterface $user, DatabaseCategoryInterface $category): void
    {
        //check if an award is configured for this category
        if ($category->getAward() === null) {
            return;
        }

        $award = $category->getAward();

        if ($this->databaseUserRepository->hasUserCompletedCategory($user->getId(), $category->getId())) {
            $this->createUserAward->createAwardForUser($user, $award);
        }
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
