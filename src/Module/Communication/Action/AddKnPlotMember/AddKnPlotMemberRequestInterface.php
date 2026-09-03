<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddKnPlotMember;

interface AddKnPlotMemberRequestInterface
{
    public function getPlotId(): int;

    public function getRecipientId(): int;
}
