<?php

declare(strict_types=1);

namespace Stu\Module\Api\Middleware;

use Exception;
use Mockery\MockInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Exception\HttpUnauthorizedException;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class SessionTest extends StuTestCase
{

    /**
     * @var null|MockInterface|UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var null|Session
     */
    private $session;

    public function setUp(): void
    {
        $this->userRepository = $this->mock(UserRepositoryInterface::class);

        $this->session = new Session(
            $this->userRepository
        );
    }

    public function testGetUserThrowsExceptionIfNotValidated(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User not validated');

        $this->session->getUser();
    }

    public function testResumeSessionThrowsExceptionOnValidationError(): void
    {
        $this->expectException(HttpUnauthorizedException::class);

        $request = $this->mock(ServerRequestInterface::class);

        $request->shouldReceive('getAttribute')
            ->with('token')
            ->once()
            ->andReturn([]);

        $this->session->resumeSession($request);
    }

    public function testResumeSessionSetsUser(): void
    {
        $request = $this->mock(ServerRequestInterface::class);
        $user = $this->mock(UserInterface::class);

        $userId = 666;

        $request->shouldReceive('getAttribute')
            ->with('token')
            ->once()
            ->andReturn(['stu' => (object)['uid' => (string)$userId]]);

        $this->userRepository->shouldReceive('find')
            ->with($userId)
            ->once()
            ->andReturn($user);

        $this->session->resumeSession($request);

        $this->assertSame(
            $user,
            $this->session->getUser()
        );
    }
}
