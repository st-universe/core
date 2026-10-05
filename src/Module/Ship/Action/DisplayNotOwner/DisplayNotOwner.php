<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DisplayNotOwner;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\GameController;

final class DisplayNotOwner implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_NOT_OWNER';

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $context->getInfo()->addInformation(_('Du bist nicht Besitzer dieses Schiffes'));

        $context->setView(GameController::DEFAULT_VIEW);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
