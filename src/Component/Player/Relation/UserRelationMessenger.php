<?php

declare(strict_types=1);

namespace Stu\Component\Player\Relation;

use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\User;

final class UserRelationMessenger
{
    public function __construct(
        private readonly AllianceActionManagerInterface $allianceActionManager,
        private readonly PrivateMessageSenderInterface $privateMessageSender
    ) {}

    public function describeParty(User|Alliance $party): string
    {
        return $party instanceof Alliance
            ? sprintf('Die Allianz %s', $party->getName())
            : sprintf('Der Siedler %s', $party->getName());
    }

    public function sendToParty(User|Alliance $party, string $text): void
    {
        if ($party instanceof Alliance) {
            $this->allianceActionManager->sendMessage($party->getId(), $text);
            return;
        }

        $this->privateMessageSender->send(UserConstants::USER_NOONE, $party->getId(), $text);
    }
}
