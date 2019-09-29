<?php

declare(strict_types=1);

namespace Stu\Module\Api\V1\Common\Login;

use Mockery;
use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Stu\Component\ErrorHandling\ErrorCodeEnum;
use Stu\Lib\LoginException;
use Stu\Lib\SessionInterface;
use Stu\Module\Api\Middleware\Request\JsonSchemaRequestInterface;
use Stu\StuApiV1TestCase;

class LoginTest extends StuApiV1TestCase
{

    /**
     * @var null|MockInterface|SessionInterface
     */
    private $session;

    /**
     * @var null|MockInterface|JsonSchemaRequestInterface
     */
    private $jsonSchemaRequest;

    /**
     * @var null|MockInterface|ConfigInterface
     */
    private $config;

    public function setUp(): void
    {
        $this->session = $this->mock(SessionInterface::class);
        $this->jsonSchemaRequest = $this->mock(JsonSchemaRequestInterface::class);
        $this->config = $this->mock(ConfigInterface::class);

        $this->setUpApiHandler(
            new Login(
                $this->session,
                $this->jsonSchemaRequest,
                $this->config
            )
        );
    }

    public function testLoginThrowsError(): void
    {
        $username = 'some-username';
        $password = 'some-password';
        $error = 'some-error';

        $this->jsonSchemaRequest->shouldReceive('getData')
            ->with($this->handler)
            ->once()
            ->andReturn((object) ['username' => $username, 'password' => $password]);

        $this->session->shouldReceive('login')
            ->with($username, $password)
            ->once()
            ->andThrow(new LoginException($error));

        $this->response->shouldReceive('withError')
            ->with(
                ErrorCodeEnum::AUTHENTICATION_FAILED,
                $error
            )
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }


    public function testLoginReturnsToken(): void
    {
        $username = 'some-username';
        $password = 'some-password';
        $jwtValidityPeriod = 666;
        $jwtSecret = 'some-secret';
        $userId = 42;

        $this->jsonSchemaRequest->shouldReceive('getData')
            ->with($this->handler)
            ->once()
            ->andReturn((object) ['username' => $username, 'password' => $password]);

        $this->session->shouldReceive('login')
            ->with($username, $password)
            ->once();
        $this->session->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->config->shouldReceive('get')
            ->with('api.jwt_validity_period')
            ->once()
            ->andReturn($jwtValidityPeriod);
        $this->config->shouldReceive('get')
            ->with('api.jwt_secret')
            ->once()
            ->andReturn($jwtSecret);

        $this->response->shouldReceive('withData')
            ->with(Mockery::on(function ($value): bool {
                $this->assertArrayHasKey('token', $value);
                $this->assertIsString($value['token']);

                return true;
            }))
            ->once()
            ->andReturnSelf();

        $this->performAssertion();
    }
}
