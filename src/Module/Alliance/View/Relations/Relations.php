<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Relations;

use AccessViolation;
use Alliance;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;

final class Relations implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_RELATIONS';

    private $allianceRelationRepository;

    public function __construct(
        AllianceRelationRepositoryInterface $allianceRelationRepository
    ) {
        $this->allianceRelationRepository = $allianceRelationRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();
        $allianceId = (int) $alliance->getId();

        if (!$alliance->currentUserIsDiplomatic()) {
            throw new AccessViolation();
        }

        $result = $this->allianceRelationRepository->getByAlliance($allianceId);

        $relations = [];
        foreach ($result as $key => $obj) {
            $relations[$key] = [
                'relation' => $obj,
                'opponent' => $obj->getRecipientId() == $alliance->getId() ? $obj->getAlliance() : $obj->getOpponent()
            ];
        }

        $game->setPageTitle(_('Diplomatie'));
        $game->appendNavigationPart(
            'alliance.php',
            _('Allianz')
        );
        $game->appendNavigationPart(
            'alliance.php?SHOW_RELATIONS=1',
            _('Diplomatie')
        );
        $game->setTemplateFile('html/alliancerelations.xhtml');
        $game->setTemplateVar('ALLIANCE_LIST', Alliance::getList());
        $game->setTemplateVar('RELATIONS', $relations);
    }
}
