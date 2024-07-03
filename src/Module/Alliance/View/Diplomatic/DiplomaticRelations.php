<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Diplomatic;

use Override;
use Stu\Component\Alliance\Relations\Renderer\AllianceRelationRendererInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

/**
 * Renders a overview of all diplomatic relations between alliances
 */
final class DiplomaticRelations implements ViewControllerInterface
{
    /** @var int */
    private const int GRAPH_WIDTH = 800;

    /** @var int */
    private const int GRAPH_HEIGHT = 700;

    /**
     * @var string
     */
    public const string VIEW_IDENTIFIER = 'SHOW_DIPLOMATIC_RELATIONS';

    public function __construct(private AllianceRelationRepositoryInterface $allianceRelationRepository, private AllianceRelationRendererInterface $allianceRelationRenderer)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle('Diplomatische Beziehungen');
        $game->setNavigation([
            [
                'url' => 'alliance.php',
                'title' => 'Allianz',
            ],
            [
                'url' => 'alliance.php?showlist=1',
                'title' => 'Allianzliste',
            ],
            [
                'url' => sprintf('alliance.php?%s=1', static::VIEW_IDENTIFIER),
                'title' => 'Diplomatische Beziehungen'
            ]
        ]);
        $game->setTemplateFile('html/alliance_diplomatic_relations.xhtml');
        $game->setTemplateVar(
            'RELATIONS_IMAGE',
            $this->allianceRelationRenderer->render(
                $this->allianceRelationRepository->findAll(),
                self::GRAPH_WIDTH,
                self::GRAPH_HEIGHT
            )
        );
    }
}
