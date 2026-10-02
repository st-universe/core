<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\DeleteAccount;

use Stu\Component\Player\Deletion\Confirmation\RequestDeletionConfirmation;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;

final class DeleteAccount implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_ACCOUNT';

    public function __construct(private RequestDeletionConfirmation $requestDeletionConfirmation) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $this->requestDeletionConfirmation->request($context->getUser());

        $context->getInfo()->addInformation(
            _('Dein Account wurde zur Löschung vorgemerkt. Zur engültigen Bestätigung wurde Dir eine Email geschickt.')
        );
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
