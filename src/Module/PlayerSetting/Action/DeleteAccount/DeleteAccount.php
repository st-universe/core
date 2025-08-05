<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\DeleteAccount;

use Override;
use Stu\Component\Player\Deletion\Confirmation\RequestDeletionConfirmation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class DeleteAccount implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_ACCOUNT';

    public function __construct(private RequestDeletionConfirmation $requestDeletionConfirmation) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $this->requestDeletionConfirmation->request($game->getUser());

        $game->getInfo()->addInformation(
            _('Dein Account wurde zur Löschung vorgemerkt. Zur engültigen Bestätigung wurde Dir eine Email geschickt.')
        );
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
