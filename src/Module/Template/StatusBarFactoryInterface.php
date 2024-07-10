<?php

declare(strict_types=1);

namespace Stu\Module\Template;

/**
 * Creates tal component classes (like the status bar)
 */
interface StatusBarFactoryInterface
{
    public function createStatusBar(): StatusBarInterface;
}
