<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\Lib;

use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionClass;
use ReflectionNamedType;
use request;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceBoard;
use Stu\Orm\Entity\AllianceBoardPost;
use Stu\Orm\Entity\AllianceBoardTopic;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceBoardPostRepositoryInterface;
use Stu\Orm\Repository\AllianceBoardRepositoryInterface;
use Stu\Orm\Repository\AllianceBoardTopicRepositoryInterface;
use Stu\StuTestCase;

final class AllianceForumAuthorizationTest extends StuTestCase
{
    public static function provideForumRequests(): iterable
    {
        foreach (['EditPost', 'DeletePost', 'DeleteTopic', 'DeleteBoard', 'RenameBoard', 'RenameTopic', 'SetTopicSticky', 'UnsetTopicSticky', 'ShowEditPost', 'BoardSettings', 'TopicSettings'] as $handler) {
            foreach (['author', 'ordinary', 'editor', 'outsider'] as $role) {
                yield $handler . '-' . $role => [$handler, $role];
            }
        }
    }

    #[DataProvider('provideForumRequests')]
    public function testAuthorAndEditorPermissionsOnEveryEntryPoint(string $handler, string $role): void
    {
        $previousMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        request::setMockVars(['pid' => '3', 'text' => 'Neuer Text']);
        $isView = in_array($handler, ['ShowEditPost', 'BoardSettings', 'TopicSettings']);
        $isAuthorOperation = in_array($handler, ['EditPost', 'ShowEditPost']);
        $allowed = $isAuthorOperation ? $role === 'author' : $role === 'editor';
        $context = $this->mock($isView ? ViewControllerContext::class : ActionControllerContext::class);
        $user = $this->mock(User::class);
        $author = $this->mock(User::class);
        $alliance = $this->mock(Alliance::class);
        $otherAlliance = $this->mock(Alliance::class);
        $board = $this->mock(AllianceBoard::class);
        $topic = $this->mock(AllianceBoardTopic::class);
        $post = $this->mock(AllianceBoardPost::class);
        $posts = $this->mock(AllianceBoardPostRepositoryInterface::class);
        $topics = $this->mock(AllianceBoardTopicRepositoryInterface::class);
        $boards = $this->mock(AllianceBoardRepositoryInterface::class);
        $manager = $this->mock(AllianceJobManagerInterface::class);
        $alliance->shouldReceive('getId')->andReturn(42);
        $otherAlliance->shouldReceive('getId')->andReturn(99);
        $user->shouldReceive('getId')->andReturn(101);
        $user->shouldReceive('getAlliance')->andReturn($role === 'outsider' ? $otherAlliance : $alliance);
        $author->shouldReceive('getId')->andReturn($role === 'author' ? 101 : 102);
        $context->shouldReceive('getUser')->andReturn($user);
        $board->shouldReceive('getAlliance')->andReturn($alliance);
        $board->shouldReceive('getId')->andReturn(1);
        $topic->shouldReceive('getAlliance')->andReturn($alliance);
        $topic->shouldReceive('getId')->andReturn(2);
        $topic->shouldReceive('getBoardId')->andReturn(1);
        $post->shouldReceive('getBoard')->andReturn($board);
        $post->shouldReceive('getTopic')->andReturn($topic);
        $post->shouldReceive('getUser')->andReturn($author);
        $post->shouldReceive('getId')->andReturn(3);
        $manager->shouldReceive('hasUserPermission')->with($user, $alliance, AllianceJobPermissionEnum::EDIT_ALLIANCE)->andReturn($role === 'editor');
        $policy = new AllianceForumPermission($manager);

        $type = $isView ? 'View' : 'Action';
        $class = 'Stu\\Module\\Alliance\\' . $type . '\\' . $handler . '\\' . $handler;
        $reflection = new ReflectionClass($class);
        $dependencies = [];
        foreach ($reflection->getConstructor()->getParameters() as $parameter) {
            $parameterType = $parameter->getType();
            self::assertInstanceOf(ReflectionNamedType::class, $parameterType);
            $dependency = $parameterType->getName();
            $dependencies[] = match ($dependency) {
                AllianceForumPermission::class => $policy,
                AllianceBoardPostRepositoryInterface::class => $posts,
                AllianceBoardTopicRepositoryInterface::class => $topics,
                AllianceBoardRepositoryInterface::class => $boards,
                default => $this->createRequest($dependency)
            };
        }
        if (str_contains($handler, 'Post')) {
            $posts->shouldReceive('find')->with(3)->once()->andReturn($post);
        } elseif (str_contains($handler, 'Board')) {
            $boards->shouldReceive('find')->with(1)->once()->andReturn($board);
        } else {
            $topics->shouldReceive('find')->with(2)->once()->andReturn($topic);
        }

        if (!$allowed) {
            foreach ([$posts, $topics, $boards] as $repository) {
                $repository->shouldNotReceive('save');
                $repository->shouldNotReceive('delete');
            }
            $post->shouldNotReceive('setText');
            $topic->shouldNotReceive('setSticky');
            $this->expectException(AccessViolationException::class);
        } elseif ($isView) {
            $context->shouldReceive('setPageTitle')->once();
            if ($handler === 'ShowEditPost') {
                $context->shouldReceive('setViewTemplate')->once();
                $context->shouldReceive('appendNavigationPart')->times(4);
                $context->shouldReceive('setTemplateVar')->with('POST', $post)->once();
            } else {
                $context->shouldReceive('setMacroInAjaxWindow')->once();
                $context->shouldReceive('setTemplateVar')->with($handler === 'BoardSettings' ? 'BOARD' : 'TOPIC', $handler === 'BoardSettings' ? $board : $topic)->once();
            }
        } else {
            $context->shouldReceive('setView')->once();
            $context->shouldReceive('getInfo->addInformation')->once();
            match ($handler) {
                'EditPost' => $this->expectPostEdit($post, $posts),
                'DeletePost' => $this->expectPostDelete($post, $topic, $posts),
                'DeleteTopic' => $topics->shouldReceive('delete')->with($topic)->once(),
                'DeleteBoard' => $boards->shouldReceive('delete')->with($board)->once(),
                'RenameTopic' => $this->expectRename($topic, $topics),
                'RenameBoard' => $this->expectRename($board, $boards),
                default => $this->expectSticky($topic, $topics, $handler === 'SetTopicSticky')
            };
        }

        try {
            $subject = $reflection->newInstanceArgs($dependencies);
            if (!$isView) {
                self::assertTrue($subject->performSessionCheck());
            }
            $subject->handle($context);
        } finally {
            if ($previousMethod === null) {
                unset($_SERVER['REQUEST_METHOD']);
            } else {
                $_SERVER['REQUEST_METHOD'] = $previousMethod;
            }
        }
    }

    private function createRequest(string $type): MockInterface
    {
        $request = $this->mock($type);
        if (method_exists($request, 'getBoardId')) {
            $request->shouldReceive('getBoardId')->andReturn(1);
        }
        if (method_exists($request, 'getTopicId')) {
            $request->shouldReceive('getTopicId')->andReturn(2);
        }
        if (method_exists($request, 'getTitle')) {
            $request->shouldReceive('getTitle')->andReturn('Neuer Name');
        }
        return $request;
    }

    private function expectPostEdit(MockInterface $post, MockInterface $repository): void
    {
        $post->shouldReceive('setText')->with('Neuer Text')->once();
        $post->shouldReceive('setEditDate')->once();
        $repository->shouldReceive('save')->with($post)->once();
    }

    private function expectPostDelete(MockInterface $post, MockInterface $topic, MockInterface $repository): void
    {
        $topic->shouldReceive('getPostCount')->andReturn(2);
        $repository->shouldReceive('delete')->with($post)->once();
    }

    private function expectRename(MockInterface $entity, MockInterface $repository): void
    {
        $entity->shouldReceive('setName')->with('Neuer Name')->once();
        $repository->shouldReceive('save')->with($entity)->once();
    }

    private function expectSticky(MockInterface $topic, MockInterface $repository, bool $sticky): void
    {
        $topic->shouldReceive('setSticky')->with($sticky)->once();
        $repository->shouldReceive('save')->with($topic)->once();
    }
}
