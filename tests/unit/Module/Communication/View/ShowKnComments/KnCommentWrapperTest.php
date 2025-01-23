<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnComments;

use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Override;
use Stu\Orm\Entity\KnCommentInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class KnCommentWrapperTest extends StuTestCase
{
    /** @var MockInterface&KnCommentInterface */
    private MockInterface $comment;

    /** @var MockInterface&UserInterface */
    private MockInterface $user;

    /** @var MockInterface&ConfigInterface */
    private MockInterface $config;

    private KnCommentWrapper $tal;

    #[Override]
    protected function setUp(): void
    {
        $this->comment = $this->mock(KnCommentInterface::class);
        $this->user = $this->mock(UserInterface::class);
        $this->config = $this->mock(ConfigInterface::class);

        $this->tal = new KnCommentWrapper(
            $this->config,
            $this->comment,
            $this->user
        );
    }

    public function testGetIdReturnsValue(): void
    {
        $value = 666;

        $this->comment->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        static::assertSame(
            $value,
            $this->tal->getId()
        );
    }

    public function testGetKnIdReturnsValue(): void
    {
        $value = 666;

        $this->comment->shouldReceive('getPosting->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        static::assertSame(
            $value,
            $this->tal->getKnId()
        );
    }

    public function testGetTextReturnsValue(): void
    {
        $value = 'some-comment';

        $this->comment->shouldReceive('getText')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        static::assertSame(
            $value,
            $this->tal->getText()
        );
    }

    public function testGetDateReturnsValue(): void
    {
        $value = 666;

        $this->comment->shouldReceive('getDate')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        static::assertSame(
            $value,
            $this->tal->getDate()
        );
    }

    public function testGetUserIdReturnsValue(): void
    {
        $value = 666;

        $this->comment->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        static::assertSame(
            $value,
            $this->tal->getUserId()
        );
    }

    public function testGetDisplayUserNameReturnsFixedNameAfterDeletion(): void
    {
        $userName = 'some-user-name';

        $this->comment->shouldReceive('getUsername')
            ->withNoArgs()
            ->once()
            ->andReturn($userName);

        static::assertSame(
            $userName,
            $this->tal->getDisplayUserName()
        );
    }

    public function testGetDisplayUserNameReturnsTheActualUserName(): void
    {
        $userName = 'some-user-name';

        $this->comment->shouldReceive('getUser->getName')
            ->withNoArgs()
            ->once()
            ->andReturn($userName);

        $this->comment->shouldReceive('getUsername')
            ->withNoArgs()
            ->once()
            ->andReturn('');

        static::assertSame(
            $userName,
            $this->tal->getDisplayUserName()
        );
    }

    public function testGetUserAvatarPathReturnsEmptyStringOnDeletedUser(): void
    {
        $this->comment->shouldReceive('getUsername')
            ->withNoArgs()
            ->andReturn('some-name');

        static::assertSame(
            '',
            $this->tal->getUserAvatarPath()
        );
    }


    public function testGetUserAvatarPathReturnsActualUserAvatarPath(): void
    {
        $avatarPath = 'some-path';
        $basePath = 'some-base-path';

        $this->comment->shouldReceive('getUsername')
            ->withNoArgs()
            ->once()
            ->andReturn('');

        $this->comment->shouldReceive('getUser->getAvatar')
            ->withNoArgs()
            ->times(2)
            ->andReturn($avatarPath);

        $this->config->shouldReceive('get')
            ->with('game.user_avatar_path')
            ->once()
            ->andReturn($basePath);

        static::assertSame(
            sprintf(
                '/%s/%s.png',
                $basePath,
                $avatarPath
            ),
            $this->tal->getUserAvatarPath()
        );
    }

    public function testIsDeleteableReturnsFalseIfCommentBelongsToOtherUser(): void
    {
        $otherUser = $this->mock(UserInterface::class);

        $this->comment->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($otherUser);

        static::assertFalse(
            $this->tal->isDeleteable()
        );
    }

    public function testIsDeleteableReturnsTrueIfCommentBelongsToCurrentUser(): void
    {
        $this->comment->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);

        static::assertTrue(
            $this->tal->isDeleteable()
        );
    }
}
