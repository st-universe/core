<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddKnPlotMember;

use Override;
use Stu\Lib\Request\CustomControllerHelperTrait;

final class AddKnPlotMemberRequest implements AddKnPlotMemberRequestInterface
{
    use CustomControllerHelperTrait;

    #[Override]
    public function getPlotId(): int
    {
        return $this->parameter('plotid')->int()->required();
    }

    #[Override]
    public function getRecipientId(): int
    {
        return $this->parameter('memid')->int()->required();
    }
}
