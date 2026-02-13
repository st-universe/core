<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Diplomatic;

use Stu\Component\Alliance\Relations\Renderer\AllianceRelationRendererInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

/**
 * Renders a overview of all diplomatic relations between alliances
 */
final class DiplomaticRelations implements ViewControllerInterface
{
    private const int GRAPH_WIDTH = 800;

    private const int GRAPH_HEIGHT = 700;

    public const string VIEW_IDENTIFIER = 'SHOW_DIPLOMATIC_RELATIONS';

    public function __construct(private AllianceRelationRepositoryInterface $allianceRelationRepository, private AllianceRelationRendererInterface $allianceRelationRenderer) {}

    #[\Override]
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
                'url' => sprintf('alliance.php?%s=1', self::VIEW_IDENTIFIER),
                'title' => 'Diplomatische Beziehungen'
            ]
        ]);
        $game->setViewTemplate('html/alliance/alliance_diplomatic_relations.twig');
        $game->setTemplateVar(
            'RELATIONS_IMAGE',
            $this->allianceRelationRenderer->render(
                $this->allianceRelationRepository->getActive(),
                self::GRAPH_WIDTH,
                self::GRAPH_HEIGHT
            )
        );
    }
}
