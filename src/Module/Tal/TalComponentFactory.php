<?php

declare(strict_types=1);

namespace Stu\Module\Tal;

use Override;

/**
 * Creates several tal components for rendering purposes
 */
final class TalComponentFactory implements TalComponentFactoryInterface
{
    #[Override]
    public function createTalStatusBar(): TalStatusBarInterface
    {
        return new TalStatusBar();
    }
}
