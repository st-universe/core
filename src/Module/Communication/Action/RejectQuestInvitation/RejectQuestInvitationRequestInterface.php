<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\RejectQuestInvitation;

interface RejectQuestInvitationRequestInterface
{
    public function getQuestId(): int;
}
