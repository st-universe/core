<?php

declare(strict_types=1);

namespace Stu\Module\Control;

/**
 * Abstract action class for actions requiring authentication
 */
abstract class AuthenticatedActionController implements ActionControllerInterface
{
    public function performSessionCheck(): bool
    {
        return true;
    }
}
