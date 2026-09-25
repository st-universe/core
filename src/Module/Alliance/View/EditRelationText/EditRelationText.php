<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\EditRelationText;

use Stu\Component\Player\Relation\UserRelationManagerInterface;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\RelationRepositoryInterface;

final class EditRelationText implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'EDIT_RELATION_TEXT';

    public function __construct(
        private RelationRepositoryInterface $allianceRelationRepository,
        private UserRelationManagerInterface $userRelationManager,
        private EditRelationTextRequestInterface $editRelationTextRequest
    ) {}

    #[\Override]
    public function handle(ViewControllerContext $game): void
    {
        $relationId = $this->editRelationTextRequest->getRelationId();
        if ($relationId === 0) {
            return;
        }

        $user = $game->getUser();
        $relation = $this->allianceRelationRepository->find($relationId);

        if ($relation === null) {
            return;
        }

        if (!$this->userRelationManager->canEditRelationContract($user, $relation)) {
            throw new AccessViolationException();
        }

        $game->setPageTitle('Vertragstext bearbeiten');
        $game->setMacroInAjaxWindow('html/alliance/editRelationText.twig');
        $game->setTemplateVar('RELATION', $relation);
    }
}
