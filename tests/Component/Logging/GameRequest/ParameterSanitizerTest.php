<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Orm\Entity\GameRequestInterface;
use Stu\StuTestCase;

class ParameterSanitizerTest extends StuTestCase
{
    private ParameterSanitizer $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->subject = new ParameterSanitizer();
    }

    #[DataProvider('parameterDataProvider')]
    public function testSanitizeCleans(
        array $parameter,
        array $expected
    ): void {
        $gameRequest = $this->mock(GameRequestInterface::class);
        $gameRequest->shouldReceive('getParameterArray')
            ->withNoArgs()
            ->once()
            ->andReturn($parameter);
        $gameRequest->shouldReceive('setParameterArray')
            ->with($expected)
            ->once();

        static::assertSame(
            $gameRequest,
            $this->subject->sanitize($gameRequest)
        );
    }

    public static function parameterDataProvider(): array
    {
        return [
            [['_' => 'foo', 'meh' => 'bar'], ['meh' => 'bar']],
            [['sstr' => 'foo', 'meh' => 'bar'], ['meh' => 'bar']],
            [['login' => 'foo', 'meh' => 'bar'], ['meh' => 'bar']],
            [['pass' => 'foo', 'meh' => 'bar'], ['meh' => 'bar']],
            [['pass2' => 'foo', 'meh' => 'bar'], ['meh' => 'bar']],
        ];
    }
}
