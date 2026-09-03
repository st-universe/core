<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\BoardSettings;

interface BoardSettingsRequestInterface
{
    public function getBoardId(): int;
}
