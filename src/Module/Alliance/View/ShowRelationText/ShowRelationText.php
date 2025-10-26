<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\ShowRelationText;

use Stu\Exception\AccessViolationException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

final class ShowRelationText implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_RELATION_TEXT';

    public function __construct(
        private AllianceRelationRepositoryInterface $allianceRelationRepository,
        private ShowRelationTextRequestInterface $showRelationTextRequest
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $relationId = $this->showRelationTextRequest->getRelationId();
        if ($relationId === 0) {
            return;
        }

        $relation = $this->allianceRelationRepository->find($relationId);

        if ($relation === null) {
            return;
        }

        $game->setPageTitle('Vertragswerk');
        $game->setMacroInAjaxWindow('html/alliance/showRelationText.twig');
        $game->setTemplateVar('RELATION', $relation);
    }
}
