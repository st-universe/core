<?php

declare(strict_types=1);

namespace Stu\Module\Tal;

/**
 * Creates several tal components for rendering purposes
 */
final class TalComponentFactory implements TalComponentFactoryInterface
{
    public function createTalStatusBar(): TalStatusBarInterface
    {
        return new TalStatusBar();
    }
}