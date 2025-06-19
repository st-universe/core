<?php

declare(strict_types=1);

namespace Stu\Component\Logging\GameRequest;

use Mockery\MockInterface;
use Override;
use Stu\Component\Logging\GameRequest\Adapter\GameRequestLoggerInterface;
use Stu\Orm\Entity\GameRequestInterface;
use Stu\StuTestCase;

class GameRequestSaverTest extends StuTestCase
{
    private MockInterface&GameRequestLoggerInterface $gameRequestLogger;

    private MockInterface&ParameterSanitizerInterface $parameterSanitizer;

    private GameRequestSaver $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->parameterSanitizer = $this->mock(ParameterSanitizerInterface::class);
        $this->gameRequestLogger = $this->mock(GameRequestLoggerInterface::class);

        $this->subject = new GameRequestSaver(
            $this->parameterSanitizer,
            $this->gameRequestLogger
        );
    }

    public function testSaveSavesInfo(): void
    {
        $gameRequest = $this->mock(GameRequestInterface::class);
        $sanitizedGameRequest = $this->mock(GameRequestInterface::class);

        $this->parameterSanitizer->shouldReceive('sanitize')
            ->with($gameRequest)
            ->once()
            ->andReturn($sanitizedGameRequest);

        $this->gameRequestLogger->shouldReceive('info')
            ->with($sanitizedGameRequest)
            ->once();

        $this->subject->save($gameRequest);
    }

    public function testSaveSavesError(): void
    {
        $gameRequest = $this->mock(GameRequestInterface::class);
        $sanitizedGameRequest = $this->mock(GameRequestInterface::class);

        $this->parameterSanitizer->shouldReceive('sanitize')
            ->with($gameRequest)
            ->once()
            ->andReturn($sanitizedGameRequest);

        $this->gameRequestLogger->shouldReceive('error')
            ->with($sanitizedGameRequest)
            ->once();

        $this->subject->save($gameRequest, true);
    }
}
