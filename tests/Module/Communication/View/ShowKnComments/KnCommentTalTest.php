<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnComments;

use Mockery\MockInterface;
use Stu\Orm\Entity\KnCommentInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\StuTestCase;

class KnCommentTalTest extends StuTestCase
{

    /**
     * @var null|MockInterface|KnCommentInterface
     */
    private $comment;

    /**
     * @var null|MockInterface|UserInterface
     */
    private $user;

    /**
     * @var null|KnCommentTal
     */
    private $tal;

    public function setUp(): void
    {
        $this->comment = $this->mock(KnCommentInterface::class);
        $this->user = $this->mock(UserInterface::class);

        $this->tal = new KnCommentTal(
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

        $this->assertSame(
            $value,
            $this->tal->getId()
        );
    }

    public function testGetPostIdReturnsValue(): void
    {
        $value = 666;

        $this->comment->shouldReceive('getPosting->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        $this->assertSame(
            $value,
            $this->tal->getPostId()
        );
    }

    public function testGetTextReturnsValue(): void
    {
        $value = 'some-comment';

        $this->comment->shouldReceive('getText')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        $this->assertSame(
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

        $this->assertSame(
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

        $this->assertSame(
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

        $this->assertSame(
            $userName,
            $this->tal->getDisplayUserName()
        );
    }

    public function testGetDisplayUserNameReturnsTheActualUserName(): void
    {
        $userName = 'some-user-name';

        $this->comment->shouldReceive('getUser->getUserName')
            ->withNoArgs()
            ->once()
            ->andReturn($userName);

        $this->comment->shouldReceive('getUsername')
            ->withNoArgs()
            ->once()
            ->andReturn('');

        $this->assertSame(
            $userName,
            $this->tal->getDisplayUserName()
        );
    }

    public function testGetUserAvatarPathReturnsEmptyStringOnDeletedUser(): void
    {
        $this->comment->shouldReceive('getUsername')
            ->withNoArgs()
            ->once()
            ->andReturn('some-name');

        $this->assertSame(
            '',
            $this->tal->getUserAvatarPath()
        );
    }


    public function testGetUserAvatarPathReturnsActualUserAvatarPath(): void
    {
        $avatarPath = 'some-path';

        $this->comment->shouldReceive('getUsername')
            ->withNoArgs()
            ->once()
            ->andReturn('');

        $this->comment->shouldReceive('getUser->getFullAvatarPath')
            ->withNoArgs()
            ->once()
            ->andReturn($avatarPath);

        $this->assertSame(
            $avatarPath,
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

        $this->assertFalse(
            $this->tal->isDeleteable()
        );
    }

    public function testIsDeleteableReturnsTrueIfCommentBelongsToCurrentUser(): void
    {
        $this->comment->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->user);

        $this->assertTrue(
            $this->tal->isDeleteable()
        );
    }
}
