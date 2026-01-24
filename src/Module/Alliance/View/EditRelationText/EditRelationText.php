<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\EditRelationText;

use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

final class EditRelationText implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'EDIT_RELATION_TEXT';

    public function __construct(
        private AllianceRelationRepositoryInterface $allianceRelationRepository,
        private AllianceJobManagerInterface $allianceJobManager,
        private EditRelationTextRequestInterface $editRelationTextRequest
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $relationId = $this->editRelationTextRequest->getRelationId();
        if ($relationId === 0) {
            return;
        }

        $user = $game->getUser();
        $alliance = $user->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException("user not in alliance");
        }

        if (!$this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::EDIT_DIPLOMATIC_DOCUMENTS)) {
            throw new AccessViolationException();
        }

        $relation = $this->allianceRelationRepository->find($relationId);

        if ($relation === null) {
            return;
        }

        if ($relation->getAlliance() !== $alliance && $relation->getOpponent() !== $alliance) {
            throw new AccessViolationException();
        }

        $game->setPageTitle('Vertragstext bearbeiten');
        $game->setMacroInAjaxWindow('html/alliance/editRelationText.twig');
        $game->setTemplateVar('RELATION', $relation);
    }
}
