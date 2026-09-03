<?php

declare(strict_types=1);

namespace Stu\Module\Control;

interface ViewWithTutorialInterface
{
    public function getViewContext(): ViewContext;
}
