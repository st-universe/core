<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddKnPlotMember;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class AddKnPlotMemberRequest implements AddKnPlotMemberRequestInterface
{
    use CustomControllerHelperTrait;

    public function getPlotId(): int
    {
        return $this->queryParameter('plotid')->int()->required();
    }

    public function getRecipientId(): int
    {
        return $this->queryParameter('memid')->int()->required();
    }
}