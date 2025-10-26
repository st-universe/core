<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeleteKnPlotMember;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class DeleteKnPlotMemberRequest implements DeleteKnPlotMemberRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getPlotId(): int
    {
        return $this->parameter('plotid')->int()->required();
    }

    #[\Override]
    public function getRecipientId(): int
    {
        return $this->parameter('memid')->int()->required();
    }
}
