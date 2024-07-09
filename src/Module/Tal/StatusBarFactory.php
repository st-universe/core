<?php

declare(strict_types=1);

namespace Stu\Module\Tal;

use Override;

/**
 * Creates several tal components for rendering purposes
 */
final class StatusBarFactory implements StatusBarFactoryInterface
{
    #[Override]
    public function createStatusBar(): StatusBarInterface
    {
        //TODO use this everywhere instead of new
        return new StatusBar();
    }
}
