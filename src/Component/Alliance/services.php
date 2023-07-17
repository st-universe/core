<?php

declare(strict_types=1);

namespace Stu\Component\Alliance;

use Stu\Component\Alliance\Event\Listener\DiplomaticRelationProposalCreationSubscriber;
use Stu\Component\Alliance\Relations\Renderer\AllianceDataToGraphAttributeConverter;
use Stu\Component\Alliance\Relations\Renderer\AllianceRelationRenderer;
use Stu\Component\Alliance\Relations\Renderer\AllianceRelationRendererInterface;
use Stu\Component\Alliance\Relations\Renderer\RelationItemVertexBuilder;

use function DI\autowire;

return [
    AllianceUserApplicationCheckerInterface::class => autowire(AllianceUserApplicationChecker::class),
    AllianceRelationRendererInterface::class => autowire(AllianceRelationRenderer::class)
        ->constructorParameter(
            'relationItemVertexBuilder',
            autowire(RelationItemVertexBuilder::class)
                ->constructorParameter(
                    'allianceDataToGraphAttributeConverter',
                    autowire(AllianceDataToGraphAttributeConverter::class)
                )
        ),
    AllianceDescriptionRendererInterface::class => autowire(AllianceDescriptionRenderer::class),
    DiplomaticRelationProposalCreationSubscriber::class => autowire(),
];
