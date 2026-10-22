<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Orm\Entity\GameRequest;
use Stu\StuTestCase;

class ParameterSanitizerTest extends StuTestCase
{
    private ParameterSanitizer $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->subject = new ParameterSanitizer();
    }

    #[DataProvider('parameterDataProvider')]
    public function testSanitizeCleans(
        array $parameter,
        array $expected
    ): void {
        $gameRequest = $this->mock(GameRequest::class);
        $gameRequest->shouldReceive('getParameterArray')
            ->withNoArgs()
            ->once()
            ->andReturn($parameter);
        $gameRequest->shouldReceive('setParameterArray')
            ->with($expected)
            ->once();

        self::assertSame(
            $gameRequest,
            $this->subject->sanitize($gameRequest)
        );
    }

    public static function parameterDataProvider(): array
    {
        return [
            [['_' => 'foo', 'meh' => 'bar'], ['meh' => 'bar']],
            [['email' => 'foo', 'meh' => 'bar'], ['meh' => 'bar']],
            [['sstr' => 'foo', 'meh' => 'bar'], ['meh' => 'bar']],
            [['login' => 'foo', 'meh' => 'bar'], ['meh' => 'bar']],
            [['loginname' => 'foo', 'meh' => 'bar'], ['meh' => 'bar']],
            [['mobile' => 'foo', 'meh' => 'bar'], ['meh' => 'bar']],
            [['pass' => 'foo', 'meh' => 'bar'], ['meh' => 'bar']],
            [['pass2' => 'foo', 'meh' => 'bar'], ['meh' => 'bar']],
            [['password' => 'foo', 'meh' => 'bar'], ['meh' => 'bar']],
            [['password2' => 'foo', 'meh' => 'bar'], ['meh' => 'bar']],
            [['TOKEN' => 'foo', 'token' => 'bar', 'ToKeN' => 'secret', 'id' => 42], ['id' => 42]],
            [['EMAIL' => 'foo', 'LOGINNAME' => 'bar', 'OldPass' => 'secret'], []],
            [['nested' => ['reset_token' => 'foo', 'items' => [['Authorization' => 'bearer', 'amount' => 3]]]], ['nested' => ['items' => [['amount' => 3]]]]],
            [['access-token' => 'foo', 'api_key' => 'bar', 'session_id' => 'secret', 'email_code' => '1234'], []],
        ];
    }
}
