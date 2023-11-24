<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Relations\Renderer;

use JBBCode\Parser;
use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Stu\Component\Faction\FactionEnum;
use Stu\Orm\Entity\AllianceInterface;
use Stu\StuTestCase;

class AllianceDataToGraphAttributeConverterTest extends StuTestCase
{
    /** @var MockInterface&Parser */
    private MockInterface $bbCodeParser;

    /** @var MockInterface&ConfigInterface */
    private MockInterface $config;

    private AllianceDataToGraphAttributeConverter $subject;

    protected function setUp(): void
    {
        $this->bbCodeParser = $this->mock(Parser::class);
        $this->config = $this->mock(ConfigInterface::class);

        $this->subject = new AllianceDataToGraphAttributeConverter(
            $this->bbCodeParser,
            $this->config
        );
    }

    public function testConvertNameConverts(): void
    {
        $name = 'some-name';
        $parsedName = 'some-parsed-name';
        $specialCharacters = '<>&"\'\\' . PHP_EOL;

        $alliance = $this->mock(AllianceInterface::class);

        $alliance->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn($name . $specialCharacters);

        $this->bbCodeParser->shouldReceive('parse')
            ->with($name . $specialCharacters)
            ->once()
            ->andReturnSelf();
        $this->bbCodeParser->shouldReceive('getAsText')
            ->withNoArgs()
            ->once()
            ->andReturn($parsedName);

        static::assertSame(
            $parsedName,
            $this->subject->convertName($alliance)
        );
    }

    public function testGetFrameColorReturnMappedFactionColor(): void
    {
        $alliance = $this->mock(AllianceInterface::class);

        $alliance->shouldReceive('getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturn(FactionEnum::FACTION_FEDERATION);

        static::assertSame(
            '#0000ff',
            $this->subject->getFrameColor($alliance)
        );
    }

    public function testGetFrameColorReturnsRgbCodeIfSet(): void
    {
        $alliance = $this->mock(AllianceInterface::class);

        $rgbCode = 'some-code';

        $alliance->shouldReceive('getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturnNull();
        $alliance->shouldReceive('getRgbCode')
            ->withNoArgs()
            ->once()
            ->andReturn($rgbCode);

        static::assertSame(
            $rgbCode,
            $this->subject->getFrameColor($alliance)
        );
    }

    public function testGetFrameColorReturnsDefaultValue(): void
    {
        $alliance = $this->mock(AllianceInterface::class);

        $default = 'some-code';

        $alliance->shouldReceive('getFactionId')
            ->withNoArgs()
            ->once()
            ->andReturnNull();
        $alliance->shouldReceive('getRgbCode')
            ->withNoArgs()
            ->once()
            ->andReturn('');

        static::assertSame(
            $default,
            $this->subject->getFrameColor($alliance, $default)
        );
    }

    public function testGetUrlReturnsAbsoluteUrl(): void
    {
        $alliance = $this->mock(AllianceInterface::class);

        $allianceId = 666;
        $base_url = 'some-url';

        $alliance->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceId);

        $this->config->shouldReceive('get')
            ->with('game.base_url')
            ->once()
            ->andReturn($base_url);

        static::assertSame(
            sprintf(
                '%s/alliance.php?id=%d',
                $base_url,
                $allianceId
            ),
            $this->subject->getUrl($alliance)
        );
    }

    public function testGetFillColorReturnsValueForNpcAlliance(): void
    {
        $alliance = $this->mock(AllianceInterface::class);

        $alliance->shouldReceive('isNpcAlliance')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        static::assertSame(
            '#2b2b2b',
            $this->subject->getFillColor($alliance)
        );
    }

    public function testGetFillColorReturnsValueForNonNpcAlliance(): void
    {
        $alliance = $this->mock(AllianceInterface::class);

        $alliance->shouldReceive('isNpcAlliance')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        static::assertSame(
            '#4b4b4b',
            $this->subject->getFillColor($alliance)
        );
    }
}
