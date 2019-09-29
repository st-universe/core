<?php

declare(strict_types=1);

namespace Stu;

use Mockery\MockInterface;
use Psr\Http\Message\ServerRequestInterface;
use Stu\Module\Api\Middleware\Action;
use Stu\Module\Api\Middleware\Response\JsonResponseInterface;

class StuApiV1TestCase extends StuTestCase
{
    /**
     * @var null|MockInterface|ServerRequestInterface
     */
    protected $request;

    /**
     * @var null|MockInterface|JsonResponseInterface
     */
    protected $response;

    protected $args = [];

    /**
     * @var null|Action
     */
    protected $handler;

    protected function setUpApiHandler(Action $handler): void
    {
        $this->request = $this->mock(ServerRequestInterface::class);
        $this->response = $this->mock(JsonResponseInterface::class);

        $this->handler = $handler;
    }

    protected function performAssertion() {
        $this->assertSame(
            $this->response,
            call_user_func($this->handler, $this->request, $this->response, $this->args)
        );
    }
}
