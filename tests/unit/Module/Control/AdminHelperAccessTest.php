<?php

declare(strict_types=1);

namespace Stu\Module\Control;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\StuTestCase;

final class AdminHelperAccessTest extends StuTestCase
{
    #[DataProvider('helperRequests')]
    public function testHelperAccess(string $helper, string $role, string $operation, string $tokenKind): void
    {
        $process = proc_open(
            [PHP_BINARY, __DIR__ . '/Fixtures/adminHelperRequest.php', $helper, $role, $operation, $tokenKind],
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

        if ($role === 'admin' && ($operation !== 'create' || $tokenKind === 'own')) {
            if (str_starts_with($operation, 'select-')) {
                self::assertStringContainsString('<form action="" method="post">', $output);
                self::assertStringContainsString('name="sstr" value="fresh-form-token"', $output);
                self::assertStringEndsWith('COMPLETED', $output);
                return;
            }
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
                    foreach ($role === 'admin' && $operation === 'create' ? ['missing', 'wrong', 'foreign', 'consumed', 'own'] : ['own'] as $tokenKind) {
                        yield $helper . '-' . $role . '-' . $operation . '-' . $tokenKind => [$helper, $role, $operation, $tokenKind];
                    }
                }
            }
        }
        yield 'ship-torpedo-selection-form' => ['createShip', 'admin', 'select-torpedo', 'own'];
        yield 'ship-selection-form' => ['createShip', 'admin', 'select-user', 'own'];
        yield 'buildplan-get-with-empty-mandatory-modules-only-renders-form' => ['createBuildplan', 'admin', 'select-rump', 'own'];
    }
}
