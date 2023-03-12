<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\DeleteAccount;

use Stu\Component\Player\Deletion\Confirmation\RequestDeletionConfirmation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class DeleteAccount implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_ACCOUNT';

    private RequestDeletionConfirmation $requestDeletionConfirmation;

    public function __construct(
        RequestDeletionConfirmation $requestDeletionConfirmation
    ) {
        $this->requestDeletionConfirmation = $requestDeletionConfirmation;
    }

    public function handle(GameControllerInterface $game): void
    {
        $this->requestDeletionConfirmation->request($game->getUser());

        $game->addInformation(
            _('Dein Account wurde zur Löschung vorgemerkt. Zur engültigen Bestätigung wurde Dir eine Email geschickt.')
        );
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
