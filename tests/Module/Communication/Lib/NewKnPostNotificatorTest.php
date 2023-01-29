<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Component\Game\GameEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Entity\RpgPlotMemberInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class NewKnPostNotificatorTest extends StuTestCase
{
    /** @var MockInterface&PrivateMessageSenderInterface */
    private MockInterface $privateMessageSender;

    private NewKnPostNotificator $subject;

    protected function setUp(): void
    {
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);

        $this->subject = new NewKnPostNotificator(
            $this->privateMessageSender
        );
    }

    public function testNotifyNotifiesAllPlotMembers(): void
    {
        $post = $this->mock(KnPostInterface::class);
        $plot = $this->mock(RpgPlotInterface::class);
        $postUser = $this->mock(UserInterface::class);
        $plotMember = $this->mock(RpgPlotMemberInterface::class);

        $userName = 'some-user';
        $plotTitle = 'some-title';
        $postUrl = 'some-url';
        $postUserId = 666;
        $memberUserId = 42;
        $messageText = sprintf(
            'Der Spieler %s hat einen neuen Beitrag zum Plot "%s" hinzugefügt.',
            $userName,
            $plotTitle
        );

        $post->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($postUser);
        $post->shouldReceive('getUrl')
            ->withNoArgs()
            ->once()
            ->andReturn($postUrl);

        $postUser->shouldReceive('getUserName')
            ->withNoArgs()
            ->once()
            ->andReturn($userName);
        $postUser->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($postUserId);

        $plotMember->shouldReceive('getUserId')
            ->withNoArgs()
            ->times(3)
            ->andReturn($postUserId, $memberUserId, $memberUserId);

        $plot->shouldReceive('getTitle')
            ->withNoArgs()
            ->once()
            ->andReturn($plotTitle);
        $plot->shouldReceive('getMembers')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$plotMember, $plotMember]));

        $this->privateMessageSender->shouldReceive('send')
            ->with(
                GameEnum::USER_NOONE,
                $memberUserId,
                $messageText,
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SYSTEM,
                $postUrl
            )
            ->once();

        $this->subject->notify($post, $plot);
    }
}
