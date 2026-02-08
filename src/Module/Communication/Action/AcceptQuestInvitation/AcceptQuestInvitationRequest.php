<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AcceptQuestInvitation;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class AcceptQuestInvitationRequest implements AcceptQuestInvitationRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getQuestId(): int
    {
        return $this->parameter('questid')->int()->required();
    }
}
