<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeleteAllPms;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeleteAllPmsRequest implements DeleteAllPmsRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getCategoryId(): int
    {
        return $this->parameter('pmcat')->int()->required();
    }
}
