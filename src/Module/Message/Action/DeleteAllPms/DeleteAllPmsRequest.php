<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeleteAllPms;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeleteAllPmsRequest implements DeleteAllPmsRequestInterface
{
    use CustomControllerHelperTrait;

    public function getCategoryId(): int
    {
        return $this->queryParameter('pmcat')->int()->required();
    }
}
