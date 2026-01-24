<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Relations;

use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Alliance\Lib\AllianceRelationItem;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\AllianceRelationRepositoryInterface;
use Stu\Orm\Repository\AllianceRepositoryInterface;

final class Relations implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_RELATIONS';

    public function __construct(
        private AllianceRelationRepositoryInterface $allianceRelationRepository,
        private AllianceJobManagerInterface $allianceJobManager,
        private AllianceRepositoryInterface $allianceRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $alliance = $user->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException("user not in alliance");
        }

        $allianceId = $alliance->getId();

        if (
            !$this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::DIPLOMATIC)
            && !$this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::EDIT_DIPLOMATIC_DOCUMENTS)
            && !$this->allianceJobManager->hasUserPermission($user, $alliance, AllianceJobPermissionEnum::CREATE_AGREEMENTS)
        ) {
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
