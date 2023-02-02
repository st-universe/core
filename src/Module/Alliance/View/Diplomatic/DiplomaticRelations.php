<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Diplomatic;

use Fhaculty\Graph\Graph;
use Graphp\GraphViz\GraphViz;
use JBBCode\Parser;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class DiplomaticRelations implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_DIPLOMATIC_RELATIONS';

    private AllianceRepositoryInterface $allianceRepository;

    private Parser $bbCodeParser;

    private AllianceRelationRepositoryInterface $allianceRelationRepository;

    public function __construct(
        AllianceRepositoryInterface $allianceRepository,
        Parser $bbCodeParser,
        AllianceRelationRepositoryInterface $allianceRelationRepository
    ) {
        $this->allianceRepository = $allianceRepository;
        $this->bbCodeParser = $bbCodeParser;
        $this->allianceRelationRepository = $allianceRelationRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle('Diplomatische Verbindungen');

        $game->appendNavigationPart(
            'alliance.php',
            _('Allianz')
        );
        $game->appendNavigationPart(
            sprintf(
                'alliance.php?%s=1',
                static::VIEW_IDENTIFIER,
            ),
            'Diplomatische Verbindungen'
        );

        $graph = new Graph();
        $graph->setAttribute('graphviz.graph.charset', 'UTF-8');
        $graph->setAttribute('graphviz.graph.bgcolor', '#121220');
        $graph->setAttribute('graphviz.graph.width', 600);
        $vertexes = [];

        foreach ($this->allianceRepository->findAll() as $alliance) {
            $vertex = $graph->createVertex($alliance->getId());
            $name = $this->bbCodeParser->parse($alliance->getName())->getAsText();
            $name = str_replace(['&', '<', '>', '"', "'", '\\', "\n"], '', $name);
            $vertex->setAttribute('graphviz.label', $name);
            $vertex->setAttribute('graphviz.fontcolor', '#9d9d9d');
            $vertex->setAttribute('graphviz.shape', 'hexagon');
            $vertex->setAttribute('graphviz.color', '#4b4b4b');

            $vertexes[$alliance->getId()] = $vertex;
        }

        foreach ($this->allianceRelationRepository->findAll() as $relation) {
            $allianceId = $relation->getAllianceId();
            $opponentId = $relation->getOpponentId();

            if (!array_key_exists($allianceId, $vertexes) || !array_key_exists($opponentId, $vertexes)) {
                continue;
            }

            switch ($relation->getType()) {
                case AllianceEnum::ALLIANCE_RELATION_WAR:
                    $color = 'red';
                    break;
                case AllianceEnum::ALLIANCE_RELATION_TRADE:
                    $color = 'yellow';
                    break;
                case AllianceEnum::ALLIANCE_RELATION_PEACE:
                    $color = 'green';
                    break;
                case AllianceEnum::ALLIANCE_RELATION_ALLIED:
                    $color = 'blue';
                    break;
                case AllianceEnum::ALLIANCE_RELATION_FRIENDS:
                    $color = 'lightblue';
                    break;
                default:
                    $color = 'white';
            }

            $edge = $vertexes[$allianceId]->createEdge($vertexes[$opponentId]);
            $edge->setAttribute('graphviz.color', $color);
        }

        $graphviz = new GraphViz();
        $graphviz->setFormat('svg');

        $game->setTemplateFile('html/alliance_diplomatic_relations.xhtml');
        $game->setTemplateVar(
            'RELATIONS_IMAGE',
            $graphviz->createImageHtml($graph)
        );
    }
}
