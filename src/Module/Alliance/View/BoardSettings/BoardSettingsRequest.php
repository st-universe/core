<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\BoardSettings;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class BoardSettingsRequest implements BoardSettingsRequestInterface
{
    use CustomControllerHelperTrait;

    public function getBoardId(): int
    {
        return $this->queryParameter('bid')->int()->required();
    }
}
