<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Exception\AccessViolationException;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Communication\Action\AdminDeleteKnPost\AdminDeleteKnPost;
use Stu\Module\Communication\Action\AdminDeleteKnPost\AdminDeleteKnPostRequestInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\GameUserRoleCheckerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\StuTestCase;

final class AdminDeleteKnPostSecurityTest extends StuTestCase
{
    #[DataProvider('roles')]
    public function testOnlyAdministratorsCanDelete(bool $allowed): void
    {
        $request = $this->mock(AdminDeleteKnPostRequestInterface::class);
        $posts = $this->mock(KnPostRepositoryInterface::class);
        $sender = $this->mock(PrivateMessageSenderInterface::class);
        $roles = $this->mock(GameUserRoleCheckerInterface::class);
        $game = $this->mock(ActionControllerContext::class);
        $roles->shouldReceive('isAdmin')->once()->andReturn($allowed);
        $subject = new AdminDeleteKnPost($request, $posts, $sender, $roles);
        self::assertTrue($subject->performSessionCheck());
        if (!$allowed) {
            $request->shouldNotReceive('getKnId');
            $posts->shouldNotReceive('find');
            $posts->shouldNotReceive('save');
            $sender->shouldNotReceive('send');
            $this->expectException(AccessViolationException::class);
        } else {
            $post = $this->mock(KnPost::class);
            $author = $this->mock(User::class);
            $admin = $this->mock(User::class);
            $game->shouldReceive('getUser')->once()->andReturn($admin);
            $admin->shouldReceive('getName')->once()->andReturn('Admin');
            $request->shouldReceive('getKnId')->once()->andReturn(7);
            $request->shouldReceive('getReason')->andReturn('Moderation');
            $posts->shouldReceive('find')->with(7)->once()->andReturn($post);
            $post->shouldReceive('getId')->once()->andReturn(7);
            $post->shouldReceive('getUser')->once()->andReturn($author);
            $author->shouldReceive('getId')->once()->andReturn(102);
            $sender->shouldReceive('send')->once()->withArgs(fn ($from, $to, $text) => $to === 102 && str_contains($text, 'Moderation'));
            $post->shouldReceive('setDeleted')->once()->withArgs(fn ($time) => $time > 0);
            $posts->shouldReceive('save')->with($post)->once();
            $info = $this->mock(InformationWrapper::class);
            $game->shouldReceive('getInfo')->once()->andReturn($info);
            $info->shouldReceive('addInformation')->once();
        }
        $subject->handle($game);
    }

    public static function roles(): iterable
    {
        yield [false];
        yield [true];
    }
}
