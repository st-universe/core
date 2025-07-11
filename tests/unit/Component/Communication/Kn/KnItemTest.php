<?php

declare(strict_types=1);

namespace Stu\Component\Communication\Kn;

use Mockery\MockInterface;
use Override;
use Stu\Module\Template\StatusBar;
use Stu\Module\Template\StatusBarColorEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\KnCommentRepositoryInterface;
use Stu\StuTestCase;

class KnItemTest extends StuTestCase
{
    private MockInterface&KnBbCodeParser $bbcodeParser;
    private MockInterface&KnCommentRepositoryInterface $knCommentRepository;
    private MockInterface&StatusBarFactoryInterface $statusBarFactory;
    private MockInterface&KnPost $post;
    /** @var null|MockInterface|User */
    private $currentUser;

    private KnItemInterface $item;

    #[Override]
    public function setUp(): void
    {
        $this->bbcodeParser = $this->mock(KnBbCodeParser::class);
        $this->knCommentRepository = $this->mock(KnCommentRepositoryInterface::class);
        $this->statusBarFactory = $this->mock(StatusBarFactoryInterface::class);
        $this->post = $this->mock(KnPost::class);
        $this->currentUser = $this->mock(User::class);

        $this->item = new KnItem(
            $this->bbcodeParser,
            $this->knCommentRepository,
            $this->statusBarFactory,
            $this->post,
            $this->currentUser
        );
    }

    public function testGetIdReturnsId(): void
    {
        $value = 666;

        $this->post->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        $this->assertSame(
            $value,
            $this->item->getId()
        );
    }

    public function testGetUserReturnsValue(): void
    {
        $user = $this->mock(User::class);

        $this->post->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $this->assertSame(
            $user,
            $this->item->getUser()
        );
    }

    public function testGetUserIdReturnsValue(): void
    {
        $value = 666;

        $this->post->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        $this->assertSame(
            $value,
            $this->item->getUserId()
        );
    }

    public function testGetTitleReturnsValue(): void
    {
        $value = 'some-title';

        $this->post->shouldReceive('getTitle')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        $this->assertSame(
            $value,
            $this->item->getTitle()
        );
    }

    public function testGetTextReturnsParsedValue(): void
    {
        $value = 'some-text';
        $parsed_value = 'some-parsed-text';

        $this->post->shouldReceive('getText')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        $this->bbcodeParser->shouldReceive('parse')
            ->with($value)
            ->once()
            ->andReturnSelf();
        $this->bbcodeParser->shouldReceive('getAsHTML')
            ->withNoArgs()
            ->once()
            ->andReturn($parsed_value);

        $this->assertSame(
            $parsed_value,
            $this->item->getText()
        );
    }

    public function testGetDateReturnsValue(): void
    {
        $value = 42;

        $this->post->shouldReceive('getDate')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        $this->assertSame(
            $value,
            $this->item->getDate()
        );
    }

    public function testGetEditDateReturnsValue(): void
    {
        $value = 42;

        $this->post->shouldReceive('getEditDate')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        $this->assertSame(
            $value,
            $this->item->getEditDate()
        );
    }

    public function testIsEditableReturnsTrueIfEditable(): void
    {
        $this->post->shouldReceive('getDate')
            ->withNoArgs()
            ->once()
            ->andReturn(time());
        $this->post->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->currentUser);

        $this->assertTrue(
            $this->item->isEditAble()
        );
    }

    public function testIsEditableReturnsFalseIfNotEditable(): void
    {
        $this->post->shouldReceive('getDate')
            ->withNoArgs()
            ->once()
            ->andReturn(time() - 1000);

        $this->assertFalse(
            $this->item->isEditAble()
        );
    }

    public function testGetPlotReturnsPlot(): void
    {
        $plot = $this->mock(RpgPlot::class);

        $this->post->shouldReceive('getRpgPlot')
            ->withNoArgs()
            ->once()
            ->andReturn($plot);

        $this->assertSame(
            $plot,
            $this->item->getPlot()
        );
    }

    public function testGetCommentCountReturnsValus(): void
    {
        $amount = 33;

        $this->knCommentRepository->shouldReceive('getAmountByPost')
            ->with($this->post)
            ->once()
            ->andReturn($amount);

        $this->assertSame(
            $amount,
            $this->item->getCommentCount()
        );
    }

    public function testDisplayContactLinksReturnsTrue(): void
    {
        $user = $this->mock(User::class);

        $this->post->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);

        $this->assertTrue(
            $this->item->displayContactLinks()
        );
    }

    public function testDisplayContactLinksReturnsFalse(): void
    {
        $this->post->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->currentUser);

        $this->assertFalse(
            $this->item->displayContactLinks()
        );
    }

    public function testGetUserNameReturnsValue(): void
    {
        $value = 'some-name';

        $this->post->shouldReceive('getUsername')
            ->withNoArgs()
            ->once()
            ->andReturn($value);

        $this->assertSame(
            $value,
            $this->item->getUserName()
        );
    }

    public function testIsNewerThanMarkReturnsTrueIfNewer(): void
    {
        $postId = 666;
        $markId = 555;

        $this->post->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($postId);

        $this->currentUser->shouldReceive('getKnMark')
            ->withNoArgs()
            ->once()
            ->andReturn($markId);

        $this->assertTrue(
            $this->item->isNewerThanMark()
        );
    }

    public function testIsNewerThanMarkReturnsFalseIfOlder(): void
    {
        $postId = 555;
        $markId = 666;

        $this->post->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($postId);

        $this->currentUser->shouldReceive('getKnMark')
            ->withNoArgs()
            ->once()
            ->andReturn($markId);

        $this->assertFalse(
            $this->item->isNewerThanMark()
        );
    }

    public function testUserHasRatedReturnsTrueIfAlreadyRated(): void
    {
        $userId = 666;

        $this->post->shouldReceive('getRatings')
            ->withNoArgs()
            ->once()
            ->andReturn([$userId => 'foo']);

        $this->currentUser->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->assertTrue(
            $this->item->userHasRated()
        );
    }

    public function testUserHasRatedReturnsFalseIfHasNotRatedYet(): void
    {
        $userId = 666;

        $this->post->shouldReceive('getRatings')
            ->withNoArgs()
            ->once()
            ->andReturn([]);

        $this->currentUser->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->assertFalse(
            $this->item->userHasRated()
        );
    }

    public function testUserCanRateReturnsFalseIfItsTheSameUser(): void
    {
        $userId = 666;

        $this->post->shouldReceive('getRatings')
            ->withNoArgs()
            ->once()
            ->andReturn([]);
        $this->post->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->currentUser);

        $this->currentUser->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->assertFalse(
            $this->item->userCanRate()
        );
    }

    public function testUserCanRateReturnsTrueIfItsRateable(): void
    {
        $userId = 666;

        $this->post->shouldReceive('getRatings')
            ->withNoArgs()
            ->once()
            ->andReturn([]);
        $this->post->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(User::class));

        $this->currentUser->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->currentUser->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->assertTrue(
            $this->item->userCanRate()
        );
    }

    public function testGetRatingReturnsRatingsSum(): void
    {
        $this->post->shouldReceive('getRatings')
            ->withNoArgs()
            ->once()
            ->andReturn([5, 10]);

        $this->assertSame(
            15,
            $this->item->getRating()
        );
    }

    public function testGetRatingBarReturnsEmptyStringIfNotRatedYet(): void
    {
        $this->post->shouldReceive('getRatings')
            ->withNoArgs()
            ->once()
            ->andReturn([]);

        $this->assertSame(
            '',
            $this->item->getRatingBar()
        );
    }

    public function testGetRatingBarReturnsBar(): void
    {
        $statusBar = $this->mock(StatusBar::class);

        $this->statusBarFactory->shouldReceive('createStatusBar')
            ->withNoArgs()
            ->once()
            ->andReturn($statusBar);

        $this->post->shouldReceive('getRatings')
            ->withNoArgs()
            ->twice()
            ->andReturn([666 => 1]);

        $statusBar->shouldReceive('setColor')
            ->with(StatusBarColorEnum::YELLOW)
            ->once()
            ->andReturnSelf();
        $statusBar->shouldReceive('setLabel')
            ->with('Bewertung')
            ->once()
            ->andReturnSelf();
        $statusBar->shouldReceive('setMaxValue')
            ->with(1)
            ->once()
            ->andReturnSelf();
        $statusBar->shouldReceive('setValue')
            ->with(1)
            ->once()
            ->andReturnSelf();
        $statusBar->shouldReceive('render')
            ->withNoArgs()
            ->once()
            ->andReturn('balken');

        $this->assertStringContainsString(
            'balken',
            $this->item->getRatingBar()
        );
    }
}
