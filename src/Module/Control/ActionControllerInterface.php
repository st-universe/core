<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Module\Control\Component\Action\ActionControllerContext;

interface ActionControllerInterface extends ControllerInterface
{
    public function handle(ActionControllerContext $context): void;

    public function performSessionCheck(): bool;
}
