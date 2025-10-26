<?php

declare(strict_types=1);

namespace Stu\Component\Alliance;

use JBBCode\Parser;
use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use Stu\Component\Alliance\Relations\Renderer\AllianceRelationRendererInterface;
use Stu\Lib\ParserWithImageInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceRelation;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\StuTestCase;

class AllianceDescriptionRendererTest extends StuTestCase
{
    private MockInterface&ParserWithImageInterface $parserWithImage;

    private MockInterface&AllianceRelationRendererInterface $allianceRelationRenderer;

    private MockInterface&AllianceRelationRepositoryInterface $allianceRelationRepository;

    private MockInterface&Alliance $alliance;

    private MockInterface&ConfigInterface $config;

    private AllianceDescriptionRenderer $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->parserWithImage = $this->mock(ParserWithImageInterface::class);
        $this->allianceRelationRenderer = $this->mock(AllianceRelationRendererInterface::class);
        $this->allianceRelationRepository = $this->mock(AllianceRelationRepositoryInterface::class);
        $this->config = $this->mock(ConfigInterface::class);

        $this->alliance = $this->mock(Alliance::class);

        $this->subject = new AllianceDescriptionRenderer(
            $this->parserWithImage,
            $this->allianceRelationRenderer,
            $this->config,
            $this->allianceRelationRepository
        );
    }

    public function testRenderReplacesHomepage(): void
    {
        $homepage = 'some-homepage';

        $this->alliance->shouldReceive('getHomepage')
            ->withNoArgs()
            ->once()
            ->andReturn($homepage);

        $this->createRendererExpectations(
            'ALLIANCE_HOMEPAGE_LINK',
            sprintf('<a href="%s" target="_blank">%s</a>', $homepage, 'Zur Allianz Homepage')
        );
    }

    public function testRenderReplacesBannerWithAvater(): void
    {
        $avatar = 'avatar_path';
        $base_path = 'base_path';

        $this->alliance->shouldReceive('getAvatar')
            ->withNoArgs()
            ->once()
            ->andReturn($avatar);

        $this->config->shouldReceive('get')
            ->with('game.alliance_avatar_path')
            ->once()
            ->andReturn($base_path);

        $this->createRendererExpectations(
            'ALLIANCE_BANNER',
            sprintf('<img src="%s/%s.png" />', $base_path, $avatar)
        );
    }

    public function testRenderReplacesPresidentsName(): void
    {
        $name = 'name';

        $this->alliance->shouldReceive('getFounder->getUser->getName')
            ->withNoArgs()
            ->once()
            ->andReturn($name);

        $this->createRendererExpectations(
            'ALLIANCE_PRESIDENT',
            $name
        );
    }

    public function testRenderReplacesVicePresidentsName(): void
    {
        $name = 'name';

        $this->alliance->shouldReceive('getSuccessor->getUser->getName')
            ->withNoArgs()
            ->once()
            ->andReturn($name);

        $this->createRendererExpectations(
            'ALLIANCE_VICEPRESIDENT',
            $name
        );
    }

    public function testRenderReplacesVicePresidentsNameWithFallbackIfNotSet(): void
    {
        $this->alliance->shouldReceive('getSuccessor')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->createRendererExpectations(
            'ALLIANCE_VICEPRESIDENT',
            'Unbesetzt'
        );
    }

    public function testRenderReplacesForeignMinistersName(): void
    {
        $name = 'name';

        $this->alliance->shouldReceive('getDiplomatic->getUser->getName')
            ->withNoArgs()
            ->once()
            ->andReturn($name);

        $this->createRendererExpectations(
            'ALLIANCE_FOREIGNMINISTER',
            $name
        );
    }

    public function testRenderReplacesForeignMinistersNameWithFallbackIfNotSet(): void
    {
        $this->alliance->shouldReceive('getDiplomatic')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->createRendererExpectations(
            'ALLIANCE_FOREIGNMINISTER',
            'Unbesetzt'
        );
    }

    public function testRenderReplacesBannerWithEmptyStringIfNotSet(): void
    {
        $this->alliance->shouldReceive('getAvatar')
            ->withNoArgs()
            ->once()
            ->andReturn('');

        $this->createRendererExpectations(
            'ALLIANCE_BANNER',
            ''
        );
    }

    public function testRenderReplacesDiplomaticRelations(): void
    {
        $value = 'some-render-result';
        $allianceId = 666;

        $relation = $this->mock(AllianceRelation::class);

        $this->allianceRelationRenderer->shouldReceive('render')
            ->with(
                [$relation],
                600,
                700
            )
            ->once()
            ->andReturn($value);

        $this->alliance->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceId);

        $this->allianceRelationRepository->shouldReceive('getActiveByAlliance')
            ->with($allianceId)
            ->once()
            ->andReturn([$relation]);

        $this->createRendererExpectations(
            'ALLIANCE_DIPLOMATIC_RELATIONS',
            $value
        );
    }

    private function createRendererExpectations(
        string $variable,
        string $replacement
    ): void {
        $description = 'some-description';
        $parsedDescription = 'some-parse-description';

        $parser = $this->mock(Parser::class);

        $this->alliance->shouldReceive('getDescription')
            ->withNoArgs()
            ->once()
            ->andReturn(sprintf('%s$%s', $description, $variable));

        $this->parserWithImage->shouldReceive('parse')
            ->with($description . $replacement)
            ->once()
            ->andReturn($parser);

        $parser->shouldReceive('getAsHTML')
            ->withNoArgs()
            ->once()
            ->andReturn($parsedDescription);

        static::assertSame(
            $parsedDescription,
            $this->subject->render($this->alliance)
        );
    }
}
