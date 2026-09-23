<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Stu\Module\Control\Component\View\ViewControllerContext;

interface ViewComponentProviderInterface
{
    public function setTemplateVariables(ViewControllerContext $game): void;
}
