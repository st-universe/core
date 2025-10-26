<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\BoardSettings;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class BoardSettingsRequest implements BoardSettingsRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getBoardId(): int
    {
        return $this->parameter('boardid')->int()->required();
    }
}
