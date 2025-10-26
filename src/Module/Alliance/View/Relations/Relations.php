<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Relations;

use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceRelationItem;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class Relations implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_RELATIONS';

    public function __construct(private AllianceRelationRepositoryInterface $allianceRelationRepository, private AllianceActionManagerInterface $allianceActionManager, private AllianceRepositoryInterface $allianceRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException("user not in alliance");
        }

        $allianceId = $alliance->getId();

        if (!$this->allianceActionManager->mayManageForeignRelations($alliance, $user)) {
            throw new AccessViolationException();
        }

        $result = $this->allianceRelationRepository->getByAlliance($allianceId);

        $relations = [];
        foreach ($result as $key => $obj) {
            $relations[$key] = new AllianceRelationItem($obj, $user);
        }

        $possibleRelationTypes = [
            AllianceRelationTypeEnum::WAR,
            AllianceRelationTypeEnum::FRIENDS,
            AllianceRelationTypeEnum::ALLIED,
            AllianceRelationTypeEnum::TRADE,
            AllianceRelationTypeEnum::VASSAL
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
        $game->setViewTemplate('html/alliance/alliancerelations.twig');
        $game->setTemplateVar('ALLIANCE_LIST', $this->allianceRepository->findAllOrdered());
        $game->setTemplateVar('RELATIONS', $relations);
        $game->setTemplateVar('POSSIBLE_RELATION_TYPES', $possibleRelationTypes);
    }
}
