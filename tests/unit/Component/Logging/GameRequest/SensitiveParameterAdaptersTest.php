<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest;

use Doctrine\DBAL\Connection;
use Monolog\Level;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Log\LoggerInterface;
use Stu\Component\Logging\GameRequest\Adapter\DatabaseAdapter;
use Stu\Component\Logging\GameRequest\Adapter\LogfileAdapter;
use Stu\Orm\Entity\GameRequest;
use Stu\StuTestCase;

final class SensitiveParameterAdaptersTest extends StuTestCase
{
    #[DataProvider('adapters')]
    public function testBothAdaptersReceiveOnlyRecursivelySanitizedParameters(bool $databaseAdapter, bool $error): void
    {
        $previousMethod = $_SERVER['REQUEST_METHOD'] ?? null;
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $gameRequest = new GameRequest()->setTime(time())->setParameterArray([
            'TOKEN' => 'sensitive-reset-value',
            'ToKeN' => 'sensitive-deletion-value',
            'settings' => ['token' => 'sensitive-nested-value', 'SECRET' => 'sensitive-secret-value', 'page' => 2],
            'COOKIE' => 'sensitive-cookie-value',
            'Authorization' => 'sensitive-authorization-value',
            'id' => 42,
        ]);
        $gameRequest->setRequestId('request-privacy-test');
        $expected = ['settings' => ['page' => 2], 'id' => 42];
        if ($databaseAdapter) {
            $database = $this->mock(Connection::class);
            $database->shouldReceive('insert')->once()->withArgs(function ($table, $values) use ($expected): bool {
                self::assertSame(GameRequest::TABLE_NAME, $table);
                $params = json_decode($values['params'], true, flags: JSON_THROW_ON_ERROR);
                unset($params['request_id'], $params['log_type']);
                self::assertSame($expected, $params);
                self::assertStringNotContainsString('sensitive-', $values['params']);
                return true;
            });
            $adapter = new DatabaseAdapter($database);
        } else {
            $logger = $this->mock(LoggerInterface::class);
            $logger->shouldReceive('log')->once()->withArgs(function ($level, $requestId, $context) use ($error, $expected): bool {
                self::assertSame($error ? Level::Error : Level::Info, $level);
                self::assertSame($expected, $context['params']);
                self::assertStringNotContainsString('sensitive-', json_encode($context, JSON_THROW_ON_ERROR));
                return true;
            });
            $adapter = new LogfileAdapter($logger);
        }
        try {
            new GameRequestSaver(new ParameterSanitizer(), $adapter)->save($gameRequest, $error);
        } finally {
            if ($previousMethod === null) {
                unset($_SERVER['REQUEST_METHOD']);
            } else {
                $_SERVER['REQUEST_METHOD'] = $previousMethod;
            }
        }
    }

    public static function adapters(): iterable
    {
        yield 'logfile info' => [false, false];
        yield 'logfile error' => [false, true];
        yield 'database info' => [true, false];
        yield 'database error' => [true, true];
    }
}
