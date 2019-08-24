<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\IgnoreUser;

use Ignorelist;
use IgnorelistData;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use User;

final class IgnoreUser implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_IGNORE_USER';

    private $ignoreUserRequest;

    public function __construct(
        IgnoreUserRequestInterface $ignoreUserRequest
    ) {
        $this->ignoreUserRequest = $ignoreUserRequest;
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
        if (Ignorelist::isOnList($userId, $recipient->getId()) == 1) {
            $game->addInformation(_('Der Spieler befindet sich bereits auf Deiner Ignoreliste'));
            return;
        }
        $ignore = new IgnorelistData();
        $ignore->setUserId($userId);
        $ignore->setDate(time());
        $ignore->setRecipientId($recipient->getId());
        $ignore->save();

        $game->addInformation(_('Der Spieler wird ignoriert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
