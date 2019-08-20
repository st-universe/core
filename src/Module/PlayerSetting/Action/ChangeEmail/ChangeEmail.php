<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeEmail;

use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;

final class ChangeEmail implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_EMAIL';

    private $changeEmailRequest;

    public function __construct(
        ChangeEmailRequestInterface $changeEmailRequest
    ) {
        $this->changeEmailRequest = $changeEmailRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $value = $this->changeEmailRequest->getEmailAddress();
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $game->addInformation(_('Die E-Mailadresse ist ungültig'));
            return;
        }

        $user = $game->getUser();

        $user->setEmail($value);
        $user->save();

        $game->addInformation(_('Deine E-Mailadresse wurde geändert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
