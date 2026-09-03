<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AcceptQuestInvitation;

interface AcceptQuestInvitationRequestInterface
{
    public function getQuestId(): int;
}
