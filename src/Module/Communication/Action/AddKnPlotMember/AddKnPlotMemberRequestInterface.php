<?php

namespace Stu\Module\Communication\Action\AddKnPlotMember;

interface AddKnPlotMemberRequestInterface
{
    public function getPlotId(): int;

    public function getRecipientId(): int;
}
