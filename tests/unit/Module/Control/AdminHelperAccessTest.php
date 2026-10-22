<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\StuTestCase;

final class AdminHelperAccessTest extends StuTestCase
{
    #[DataProvider('helperRequests')]
    public function testHelperAccess(string $helper, string $role, string $operation): void
    {
        $process = proc_open(
            [PHP_BINARY, __DIR__ . '/Fixtures/adminHelperRequest.php', $helper, $role, $operation],
            [1 => ['pipe', 'w'], 2 => ['pipe', 'w']],
            $pipes
        );
        self::assertIsResource($process);

        $output = stream_get_contents($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);

        self::assertSame(0, proc_close($process), $errors);
        self::assertSame('', $errors);

        if ($role === 'admin') {
            $expected = $operation === 'list' ? '' : match ($helper) {
                'createShip' => '1 Schiff(e) erstellt, mit Wurstblinkern!',
                'createBuildplan' => 'Bauplan angelegt'
            };
            self::assertSame($expected . 'COMPLETED', $output);
        } else {
            self::assertSame($role === 'no-session' ? 'SESSION_INVALID' : '', $output);
        }
    }

    public static function helperRequests(): iterable
    {
        foreach (['createShip', 'createBuildplan'] as $helper) {
            foreach (['player', 'missing-user', 'no-session', 'admin'] as $role) {
                foreach (['list', 'create'] as $operation) {
                    yield $helper . '-' . $role . '-' . $operation => [$helper, $role, $operation];
                }
            }
        }
    }
}
