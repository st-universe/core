<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserConstants;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Entity\RpgPlotMember;
use Stu\Orm\Entity\User;
use Stu\StuTestCase;

class NewKnPostNotificatorTest extends StuTestCase
{
    private MockInterface&PrivateMessageSenderInterface $privateMessageSender;

    private NewKnPostNotificator $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->privateMessageSender = $this->mock(PrivateMessageSenderInterface::class);

        $this->subject = new NewKnPostNotificator(
            $this->privateMessageSender
        );
    }

    public function testNotifyNotifiesAllPlotMembers(): void
    {
        $post = $this->mock(KnPost::class);
        $plot = $this->mock(RpgPlot::class);
        $postUser = $this->mock(User::class);
        $plotMember = $this->mock(RpgPlotMember::class);

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

        $postUser->shouldReceive('getName')
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
                UserConstants::USER_NOONE,
                $memberUserId,
                $messageText,
                PrivateMessageFolderTypeEnum::SPECIAL_SYSTEM,
                $postUrl
            )
            ->once();

        $this->subject->notify($post, $plot);
    }
}
