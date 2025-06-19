<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest\Adapter;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Mockery\MockInterface;
use Monolog\Logger;
use Override;
use Stu\Orm\Entity\GameRequest;
use Stu\Orm\Entity\GameRequestInterface;
use Stu\StuTestCase;

class DatabaseAdapterTest extends StuTestCase
{
    private MockInterface&Connection $database;

    private DatabaseAdapter $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->database = $this->mock(Connection::class);

        $this->subject = new DatabaseAdapter(
            $this->database
        );
    }

    public function testInfoLogs(): void
    {
        $gameRequest = $this->mock(GameRequestInterface::class);

        $requestId = 'some-request-id';
        $turnId = 666;
        $time = 112233;
        $module = 'some-module';
        $action = 'some-action';
        $view = 'some-view';
        $userId = 42;
        $actionMs = 123;
        $viewMs = 456;
        $renderMs = 678;
        $params = [
            'some-params' => 'moep',
            'request_id' => $requestId,
            'log_type' => Logger::INFO,
        ];

        $gameRequest->shouldReceive('getRequestId')
            ->withNoArgs()
            ->once()
            ->andReturn($requestId);
        $gameRequest->shouldReceive('getTime')
            ->withNoArgs()
            ->once()
            ->andReturn($time);
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

        $this->database->shouldReceive('insert')
            ->with(
                GameRequest::TABLE_NAME,
                [
                    'turn_id' => $turnId,
                    'time' => $time,
                    'module' => $module,
                    'action' => $action,
                    'action_ms' => $actionMs,
                    'view' => $view,
                    'view_ms' => $viewMs,
                    'user_id' => $userId,
                    'render_ms' => $renderMs,
                    'params' => json_encode($params, JSON_PRETTY_PRINT),
                ],
                [
                    'turn_id' => ParameterType::INTEGER,
                    'time' => ParameterType::INTEGER,
                    'module' => ParameterType::STRING,
                    'action' => ParameterType::STRING,
                    'action_ms' => ParameterType::INTEGER,
                    'view' => ParameterType::STRING,
                    'view_ms' => ParameterType::INTEGER,
                    'user_id' => ParameterType::INTEGER,
                    'render_ms' => ParameterType::INTEGER,
                    'params' => ParameterType::STRING,
                ]
            )
            ->once();

        $this->subject->info($gameRequest, false);
    }
}
