<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Relations\Renderer;

use JBBCode\Parser;
use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Override;
use Stu\Component\Faction\FactionEnum;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Faction;
use Stu\StuTestCase;

class AllianceDataToGraphAttributeConverterTest extends StuTestCase
{
    private MockInterface&Parser $bbCodeParser;

    private MockInterface&ConfigInterface $config;

    private AllianceDataToGraphAttributeConverter $subject;

    #[Override]
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

        $alliance = $this->mock(Alliance::class);

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
        $alliance = $this->mock(Alliance::class);
        $faction = $this->mock(Faction::class);

        $alliance->shouldReceive('getFaction')
            ->withNoArgs()
            ->once()
            ->andReturn($faction);
        $faction->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(FactionEnum::FACTION_FEDERATION->value);

        static::assertSame(
            '#0000ff',
            $this->subject->getFrameColor($alliance)
        );
    }

    public function testGetFrameColorReturnsRgbCodeIfSet(): void
    {
        $alliance = $this->mock(Alliance::class);

        $rgbCode = 'some-code';

        $alliance->shouldReceive('getFaction')
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
        $alliance = $this->mock(Alliance::class);

        $default = 'some-code';

        $alliance->shouldReceive('getFaction')
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
        $alliance = $this->mock(Alliance::class);

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
        $alliance = $this->mock(Alliance::class);

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
        $alliance = $this->mock(Alliance::class);

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
