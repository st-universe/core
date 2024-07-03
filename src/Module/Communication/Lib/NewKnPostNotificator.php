<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Lib;

use Override;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Entity\RpgPlotMemberInterface;

/**
 * Notifies users about new kn postings
 */
final class NewKnPostNotificator implements NewKnPostNotificatorInterface
{
    public function __construct(private PrivateMessageSenderInterface $privateMessageSender)
    {
    }

    #[Override]
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
                    UserEnum::USER_NOONE,
                    $member->getUserId(),
                    $text,
                    PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                    $url
                );
            });
    }
}
