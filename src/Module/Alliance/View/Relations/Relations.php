<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Relations;

use Stu\Exception\AccessViolation;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceRelationItem;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class Relations implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_RELATIONS';

    private AllianceRelationRepositoryInterface $allianceRelationRepository;

    private AllianceActionManagerInterface $allianceActionManager;

    private AllianceRepositoryInterface $allianceRepository;

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

        if ($alliance === null) {
            throw new AccessViolation();
        }

        $allianceId = (int) $alliance->getId();

        if (!$this->allianceActionManager->mayManageForeignRelations($alliance, $user)) {
            throw new AccessViolation();
        }

        $result = $this->allianceRelationRepository->getByAlliance($allianceId);

        $relations = [];
        foreach ($result as $key => $obj) {
            $relations[$key] = new AllianceRelationItem($obj, $user);
        }

        $possibleRelationTypes = [
            AllianceEnum::ALLIANCE_RELATION_WAR => _('Krieg'),
            AllianceEnum::ALLIANCE_RELATION_FRIENDS => _('Freundschaft'),
            AllianceEnum::ALLIANCE_RELATION_ALLIED => _('Bündnis'),
            AllianceEnum::ALLIANCE_RELATION_TRADE => _('Handelsabkommen'),
            AllianceEnum::ALLIANCE_RELATION_VASSAL => _('Vasall')
        ];

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
        $game->setTemplateVar('POSSIBLE_RELATION_TYPES', $possibleRelationTypes);
    }
}
