<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Lib;

use Stu\Component\Game\GameEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Entity\RpgPlotMemberInterface;

/**
 * Notifies users about new kn postings
 */
final class NewKnPostNotificator implements NewKnPostNotificatorInterface
{
    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->privateMessageSender = $privateMessageSender;
    }

    public function notify(KnPostInterface $post, RpgPlotInterface $plot): void
    {
        $postUser = $post->getUser();
        $url = $post->getUrl();
        $postUserId = $postUser->getId();

        $text = sprintf(
            'Der Spieler %s hat einen neuen Beitrag zum Plot "%s" hinzugefügt.',
            $postUser->getName(),
            $plot->getTitle()
        );

        // filter the postUser from the member list
        $plot->getMembers()
            ->filter(
                fn (RpgPlotMemberInterface $member): bool => $member->getUserId() !== $postUserId
            )
            ->map(function (RpgPlotMemberInterface $member) use ($text, $url): void {
                $this->privateMessageSender->send(
                    GameEnum::USER_NOONE,
                    $member->getUserId(),
                    $text,
                    PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM,
                    $url
                );
            });
    }
}
