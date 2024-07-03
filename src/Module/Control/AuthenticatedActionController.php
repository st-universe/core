<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Override;

/**
 * Abstract action class for actions requiring authentication
 */
abstract class AuthenticatedActionController implements ActionControllerInterface
{
    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
