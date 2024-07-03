<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Edit;

use Override;
use Stu\Exception\AccessViolation;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;

final class Edit implements ViewControllerInterface
{
    /**
     * @var string
     */
    public const string VIEW_IDENTIFIER = 'EDIT_ALLIANCE';

    public function __construct(private AllianceActionManagerInterface $allianceActionManager)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        if ($alliance === null) {
            throw new AccessViolation();
        }

        if (!$this->allianceActionManager->mayEdit($alliance, $game->getUser())) {
            throw new AccessViolation();
        }

        $game->setPageTitle(_('Allianz editieren'));

        $game->appendNavigationPart(
            'alliance.php',
            _('Allianz')
        );
        $game->appendNavigationPart(
            'alliance.php?EDIT_ALLIANCE=1',
            _('Editieren')
        );
        $game->setViewTemplate('html/alliance/allianceEdit.twig');
        $game->setTemplateVar('ALLIANCE', $alliance);
        $game->setTemplateVar(
            'CAN_EDIT_FACTION_MODE',
            $this->allianceActionManager->mayEditFactionMode($alliance, $game->getUser()->getFactionId())
        );
    }
}
