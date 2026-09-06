<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Diplomatic;

use Mockery\MockInterface;
use Stu\Component\Alliance\Relations\Renderer\AllianceRelationRendererInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Relation;
use Stu\Orm\Repository\RelationRepositoryInterface;
use Stu\StuTestCase;

class DiplomaticRelationsTest extends StuTestCase
{
    private MockInterface&RelationRepositoryInterface $allianceRelationRepository;

    private MockInterface&AllianceRelationRendererInterface $allianceRelationRenderer;

    private DiplomaticRelations $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->allianceRelationRepository = $this->mock(RelationRepositoryInterface::class);
        $this->allianceRelationRenderer = $this->mock(AllianceRelationRendererInterface::class);

        $this->subject = new DiplomaticRelations(
            $this->allianceRelationRepository,
            $this->allianceRelationRenderer
        );
    }

    public function testHandleRenders(): void
    {
        $game = $this->mock(GameControllerInterface::class);

        $renderResult = 'some-render-result';

        $relation = $this->mock(Relation::class);

        $game->shouldReceive('setPageTitle')->with('Diplomatische Beziehungen')->once();
        $game
            ->shouldReceive('setNavigation')
            ->with([
                [
                    'url' => 'alliance.php',
                    'title' => 'Allianz'
                ],
                [
                    'url' => 'alliance.php?showlist=1',
                    'title' => 'Allianzliste'
                ],
                [
                    'url' => sprintf('alliance.php?%s=1', DiplomaticRelations::VIEW_IDENTIFIER),
                    'title' => 'Diplomatische Beziehungen'
                ]
            ])
            ->once()
            ->andReturnSelf();

        $game
            ->shouldReceive('setViewTemplate')
            ->with('html/alliance/alliance_diplomatic_relations.twig')
            ->once();
        $game
            ->shouldReceive('setTemplateVar')
            ->with(
                'RELATIONS_IMAGE',
                $renderResult
            )
            ->once();

        $this->allianceRelationRepository
            ->shouldReceive('getActive')
            ->withNoArgs()
            ->once()
            ->andReturn([$relation]);

        $this->allianceRelationRenderer
            ->shouldReceive('render')
            ->with([$relation], 800, 700)
            ->once()
            ->andReturn($renderResult);

        $this->subject->handle($game);
    }
}
