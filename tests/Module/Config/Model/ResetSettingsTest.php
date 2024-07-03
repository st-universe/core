<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use Stu\Module\Config\StuConfigException;
use Stu\StuTestCase;

class ResetSettingsTest extends StuTestCase
{
    /** @var MockInterface|ConfigInterface */
    private ConfigInterface $config;

    /** @var MockObject|ResetSettings */
    private ResetSettings $subject;

    #[Override]
    public function setUp(): void
    {
        $this->config = $this->mock(ConfigInterface::class);

        $this->subject = new ResetSettings(null, $this->config);
    }

    public function testGetDelayInSecondsExpectConfigValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('reset.delay_in_seconds')
            ->once()
            ->andReturn(15);

        $result = $this->subject->getDelayInSeconds();

        $this->assertEquals(15, $result);
    }

    public function testGetDelayInSecondsExpectDefaultWhenNotPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('reset.delay_in_seconds')
            ->once()
            ->andReturn(null);

        $result = $this->subject->getDelayInSeconds();

        $this->assertEquals(5, $result);
    }

    public function testGetDelayInSecondsExpectErrorWhenNotAnInteger(): void
    {
        static::expectExceptionMessage('The value "foo" with path "reset.delay_in_seconds" is no valid integer.');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('reset.delay_in_seconds')
            ->once()
            ->andReturn('foo');

        $this->subject->getDelayInSeconds();
    }
}
