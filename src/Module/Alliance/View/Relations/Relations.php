<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Relations;

use AccessViolation;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class Relations implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_RELATIONS';

    private $allianceRelationRepository;

    private $allianceActionManager;

    private $allianceRepository;

    public function __construct(
        AllianceRelationRepositoryInterface $allianceRelationRepository,
        AllianceActionManagerInterface $allianceActionManager,
        AllianceRepositoryInterface $allianceRepository
    ) {
        $this->allianceRelationRepository = $allianceRelationRepository;
        $this->allianceActionManager = $allianceActionManager;
        $this->allianceRepository = $allianceRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();
        $allianceId = (int) $alliance->getId();

        if (!$this->allianceActionManager->mayManageForeignRelations($allianceId, $user->getId())) {
            throw new AccessViolation();
        }

        $result = $this->allianceRelationRepository->getByAlliance($allianceId);

        $relations = [];
        foreach ($result as $key => $obj) {
            $relations[$key] = [
                'relation' => $obj,
                'opponent' => $obj->getOpponentId() == $alliance->getId() ? $obj->getAlliance() : $obj->getOpponent()
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
        $game->setTemplateVar('ALLIANCE_LIST', $this->allianceRepository->findAllOrdered());
        $game->setTemplateVar('RELATIONS', $relations);
    }
}
