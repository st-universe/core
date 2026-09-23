<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use Stu\Module\Control\Component\View\ViewControllerContext;

interface ViewControllerInterface extends ControllerInterface {

    public function handle(ViewControllerContext $game): void;
}
