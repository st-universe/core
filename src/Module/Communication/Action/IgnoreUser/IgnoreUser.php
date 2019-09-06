<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\IgnoreUser;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\IgnoreListRepositoryInterface;
use User;

final class IgnoreUser implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_IGNORE_USER';

    private $ignoreUserRequest;

    private $ignoreListRepository;

    public function __construct(
        IgnoreUserRequestInterface $ignoreUserRequest,
        IgnoreListRepositoryInterface $ignoreListRepository
    ) {
        $this->ignoreUserRequest = $ignoreUserRequest;
        $this->ignoreListRepository = $ignoreListRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $recipient = User::getUserById($this->ignoreUserRequest->getRecipientId());

        if (!$recipient) {
            $game->addInformation(_('Dieser Spieler existiert nicht'));
            return;
        }
        if ($recipient->getId() == $userId) {
            $game->addInformation(_('Du kannst Dich nicht selbst ignorieren'));
            return;
        }
        if ($this->ignoreListRepository->exists($userId, (int) $recipient->getId()) === true) {
            $game->addInformation(_('Der Spieler befindet sich bereits auf Deiner Ignoreliste'));
            return;
        }
        $ignore = $this->ignoreListRepository->prototype();
        $ignore->setUserId($userId);
        $ignore->setDate(time());
        $ignore->setRecipientId((int) $recipient->getId());

        $this->ignoreListRepository->save($ignore);

        $game->addInformation(_('Der Spieler wird ignoriert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
