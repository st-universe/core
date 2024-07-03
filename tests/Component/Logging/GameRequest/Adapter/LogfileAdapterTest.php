<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest\Adapter;

use Override;
use Exception;
use Mockery\MockInterface;
use Monolog\Level;
use Psr\Log\LoggerInterface;
use Stu\Orm\Entity\GameRequestInterface;
use Stu\StuTestCase;

class LogfileAdapterTest extends StuTestCase
{
    /** @var MockInterface&LoggerInterface */
    private MockInterface $logger;

    protected LogfileAdapter $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->logger = $this->mock(LoggerInterface::class);

        $this->subject = new LogfileAdapter(
            $this->logger
        );
    }

    public function testInfoLogs(): void
    {
        $gameRequest = $this->mock(GameRequestInterface::class);

        $this->createLoggingExpectations($gameRequest, Level::Info);

        $this->subject->info($gameRequest);
    }

    public function testErrorLogs(): void
    {
        $gameRequest = $this->mock(GameRequestInterface::class);

        $this->createLoggingExpectations($gameRequest, Level::Error);

        $this->subject->error($gameRequest);
    }

    /**
     * @param MockInterface&GameRequestInterface $gameRequest
     */
    protected function createLoggingExpectations(
        MockInterface $gameRequest,
        Level $logLevel
    ): void {
        $requestId = 'some-request-id';
        $turnId = 666;
        $module = 'some-module';
        $action = 'some-action';
        $view = 'some-view';
        $userId = 42;
        $actionMs = 123;
        $viewMs = 456;
        $renderMs = 678;
        $params = ['some-params'];
        $errorMessage = 'some-error';

        $error = new Exception($errorMessage);

        $gameRequest->shouldReceive('getRequestId')
            ->withNoArgs()
            ->once()
            ->andReturn($requestId);
        $gameRequest->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $gameRequest->shouldReceive('getModule')
            ->withNoArgs()
            ->once()
            ->andReturn($module);
        $gameRequest->shouldReceive('getAction')
            ->withNoArgs()
            ->once()
            ->andReturn($action);
        $gameRequest->shouldReceive('getView')
            ->withNoArgs()
            ->once()
            ->andReturn($view);
        $gameRequest->shouldReceive('getTurnId')
            ->withNoArgs()
            ->once()
            ->andReturn($turnId);
        $gameRequest->shouldReceive('getActionMs')
            ->withNoArgs()
            ->once()
            ->andReturn($actionMs);
        $gameRequest->shouldReceive('getViewMs')
            ->withNoArgs()
            ->once()
            ->andReturn($viewMs);
        $gameRequest->shouldReceive('getRenderMs')
            ->withNoArgs()
            ->once()
            ->andReturn($renderMs);
        $gameRequest->shouldReceive('getParameterArray')
            ->withNoArgs()
            ->once()
            ->andReturn($params);
        $gameRequest->shouldReceive('getErrors')
            ->withNoArgs()
            ->once()
            ->andReturn([$error]);

        $this->logger->shouldReceive('log')
            ->with(
                $logLevel,
                $requestId,
                [
                    'user_id' => $userId,
                    'module' => $module,
                    'action' => $action,
                    'view' => $view,
                    'turn_id' => $turnId,
                    'timing' => [
                        'action' => $actionMs,
                        'view' => $viewMs,
                        'render' => $renderMs,
                    ],
                    'params' => $params,
                    'sanity_errors' => [
                        [
                            'message' => $errorMessage,
                            'file' => $error->getFile(),
                            'line' => $error->getLine(),
                        ]
                    ],
                ]
            )
            ->once();
    }
}
