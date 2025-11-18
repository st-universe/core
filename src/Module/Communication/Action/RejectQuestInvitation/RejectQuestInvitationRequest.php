<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\RejectQuestInvitation;

use Stu\Lib\Request\CustomControllerHelperTrait;

final class RejectQuestInvitationRequest implements RejectQuestInvitationRequestInterface
{
    use CustomControllerHelperTrait;

    #[\Override]
    public function getQuestId(): int
    {
        return $this->parameter('questid')->int()->required();
    }
}