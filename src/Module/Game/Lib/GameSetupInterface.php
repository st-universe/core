<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib;

use Stu\Module\Control\Component\View\ViewControllerContext;

interface GameSetupInterface
{
    public function setTemplateAndComponents(string $viewTemplate, ViewControllerContext $context): void;
}
