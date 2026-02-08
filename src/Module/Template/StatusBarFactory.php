<?php

declare(strict_types=1);

namespace Stu\Module\Template;

/**
 * Creates status bar objects for rendering purposes
 */
final class StatusBarFactory implements StatusBarFactoryInterface
{
    #[\Override]
    public function createStatusBar(): StatusBarInterface
    {
        return new StatusBar();
    }
}
