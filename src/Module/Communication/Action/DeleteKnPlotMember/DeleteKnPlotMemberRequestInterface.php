<?php

namespace Stu\Module\Communication\Action\DeleteKnPlotMember;

interface DeleteKnPlotMemberRequestInterface
{
    public function getPlotId(): int;

    public function getRecipientId(): int;
}