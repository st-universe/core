<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowWriteKn;

use JBBCode\CodeDefinition;
use JBBCode\CodeDefinitionSet;
use Mockery\MockInterface;
use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\RpgPlotInterface;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\StuTestCase;

class ShowWriteKnTest extends StuTestCase
{
    /** @var MockInterface&RpgPlotRepositoryInterface */
    private MockInterface $rpgPlotRepository;

    /** @var MockInterface&CodeDefinitionSet */
    private MockInterface $codeDefinitionSet;

    private ShowWriteKn $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->rpgPlotRepository = $this->mock(RpgPlotRepositoryInterface::class);
        $this->codeDefinitionSet = $this->mock(CodeDefinitionSet::class);

        $this->subject = new ShowWriteKn(
            $this->rpgPlotRepository,
            $this->codeDefinitionSet
        );
    }

    public function testHandleRendersView(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $plot = $this->mock(RpgPlotInterface::class);
        $codeDefinition = $this->mock(CodeDefinition::class);

        $userId = 666;
        $tagName = 'some-tag';

        $game->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $game->shouldReceive('setViewTemplate')
            ->with('html/communication/writeKn.twig')
            ->once();
        $game->shouldReceive('appendNavigationPart')
            ->with('comm.php', 'KommNet')
            ->once();
        $game->shouldReceive('appendNavigationPart')
            ->with(
                sprintf('comm.php?%s=1', ShowWriteKn::VIEW_IDENTIFIER),
                'Beitrag schreiben'
            )
            ->once();
        $game->shouldReceive('setPageTitle')
            ->with('Beitrag schreiben')
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('ACTIVE_RPG_PLOTS', [$plot])
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('ALLOWED_BBCODE_CHARACTERS', [$tagName])
            ->once();

        $this->rpgPlotRepository->shouldReceive('getActiveByUser')
            ->with($userId)
            ->once()
            ->andReturn([$plot]);

        $this->codeDefinitionSet->shouldReceive('getCodeDefinitions')
            ->withNoArgs()
            ->once()
            ->andReturn([$codeDefinition]);

        $codeDefinition->shouldReceive('getTagName')
            ->withNoArgs()
            ->once()
            ->andReturn($tagName);

        $this->subject->handle($game);
    }
}
