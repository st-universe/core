<?php

declare(strict_types=1);

namespace Stu\Component\Map;

use Mockery\MockInterface;
use Override;
use RuntimeException;
use Stu\Module\Config\StuConfigInterface;
use Stu\Orm\Entity\LayerInterface;
use Stu\StuTestCase;

class EncodedMapTest extends StuTestCase
{
    private MockInterface&StuConfigInterface $stuConfig;

    private EncodedMapInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->stuConfig = $this->mock(StuConfigInterface::class);

        $this->subject = new EncodedMap(
            $this->stuConfig
        );
    }

    public function testGetEncodedMapPathExpectExceptionWhenKeyMissing(): void
    {
        static::expectExceptionMessage('encoding key is missing in configuration');
        static::expectException(RuntimeException::class);

        $layer = $this->mock(LayerInterface::class);

        $this->stuConfig->shouldReceive('getGameSettings->getMapSettings->getEncryptionKey')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->getEncodedMapPath(42, $layer);
    }

    public function testGetEncodedMapPathExpectCorrectPathWhenKeyIsPresent(): void
    {
        $layer = $this->mock(LayerInterface::class);

        $this->stuConfig->shouldReceive('getGameSettings->getMapSettings->getEncryptionKey')
            ->withNoArgs()
            ->once()
            ->andReturn("12345678901234567890123456789012");

        $layer->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn("5");

        $result = $this->subject->getEncodedMapPath(42, $layer);

        $this->assertEquals('5/encoded/4d544a53/61573177/576b3957/55533943/59773d3d.png', $result);
    }
}
