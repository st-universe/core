<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\MarkPmsRead;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class MarkPmsReadRequest implements MarkPmsReadRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getCategoryId(): int
    {
        return $this->parameter('pmcat')->int()->required();
    }
}
