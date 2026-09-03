<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeleteKnPlotMember;

interface DeleteKnPlotMemberRequestInterface
{
    public function getPlotId(): int;

    public function getRecipientId(): int;
}
