<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeUserName;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class ChangeUserName implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_NAME';

    private $changeUserNameRequest;

    public function __construct(
        ChangeUserNameRequestInterface $changeUserNameRequest
    ) {
        $this->changeUserNameRequest = $changeUserNameRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $value = $this->changeUserNameRequest->getName();
        $value = strip_tags(tidyString($value));
        if (strlen($value) < 6) {
            $game->addInformation(
                sprintf(
                    _('Der Siedlername muss aus mindestens 6 Zeichen bestehen')
                )
            );
            return;
        }
        if (strlen($value) > 255) {
            $game->addInformation(
                sprintf(
                    _('Der Siedlername darf inklusive BBCode nur maximal 255 Zeichen lang sein')
                )
            );
            return;
        }
        if (strlen(strip_tags(BBCode()->parse($value))) > 60) {
            $game->addInformation(
                sprintf(
                    _('Der Siedlername darf nur maximal 60 Zeichen lang sein')
                )
            );
            return;
        }

        $user = $game->getUser();
        $user->setUser($value);
        $user->save();

        $game->addInformation(_('Dein Name wurde geändert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
