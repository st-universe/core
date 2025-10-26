<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Lib;

use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Entity\RpgPlotMember;

/**
 * Notifies users about new kn postings
 */
final class NewKnPostNotificator implements NewKnPostNotificatorInterface
{
    public function __construct(private PrivateMessageSenderInterface $privateMessageSender) {}

    #[\Override]
    public function notify(KnPost $post, RpgPlot $plot): void
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
                fn(RpgPlotMember $member): bool => $member->getUserId() !== $postUserId
            )
            ->map(function (RpgPlotMember $member) use ($text, $url): void {
                $this->privateMessageSender->send(
                    UserConstants::USER_NOONE,
                    $member->getUserId(),
                    $text,
                    PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                    $url
                );
            });
    }
}
