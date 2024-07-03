<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\IgnoreUser;

use Override;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class IgnoreUser implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_IGNORE_USER';

    public function __construct(private IgnoreUserRequestInterface $ignoreUserRequest, private IgnoreListRepositoryInterface $ignoreListRepository, private UserRepositoryInterface $userRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $recipient = $this->userRepository->find($this->ignoreUserRequest->getRecipientId());

        if ($recipient === null) {
            $game->addInformation(_('Dieser Spieler existiert nicht'));
            return;
        }
        if ($recipient->getId() === $userId) {
            $game->addInformation(_('Du kannst Dich nicht selbst ignorieren'));
            return;
        }
        if ($this->ignoreListRepository->exists($userId, $recipient->getId()) === true) {
            $game->addInformation(_('Der Spieler befindet sich bereits auf Deiner Ignoreliste'));
            return;
        }
        $ignore = $this->ignoreListRepository->prototype();
        $ignore->setUser($game->getUser());
        $ignore->setDate(time());
        $ignore->setRecipient($recipient);

        $this->ignoreListRepository->save($ignore);

        $game->addInformation(_('Der Spieler wird ignoriert'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
