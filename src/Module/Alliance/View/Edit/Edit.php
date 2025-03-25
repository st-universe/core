<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\Edit;

use Override;
use Stu\Component\Alliance\AllianceSettingsEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\AllianceSettingsInterface;

final class Edit implements ViewControllerInterface
{
    /**
     * @var string
     */
    public const string VIEW_IDENTIFIER = 'EDIT_ALLIANCE';

    public function __construct(private AllianceActionManagerInterface $allianceActionManager) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $alliance = $game->getUser()->getAlliance();

        if ($alliance === null) {
            throw new AccessViolationException();
        }

        if (!$this->allianceActionManager->mayEdit($alliance, $game->getUser())) {
            throw new AccessViolationException();
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

        $founderDescription = $alliance->getSettings()->filter(
            function (AllianceSettingsInterface $setting) {
                return $setting->getSetting() === AllianceSettingsEnum::ALLIANCE_FOUNDER_DESCRIPTION;
            }
        )->first();

        $successorDescription = $alliance->getSettings()->filter(
            function (AllianceSettingsInterface $setting) {
                return $setting->getSetting() === AllianceSettingsEnum::ALLIANCE_SUCCESSOR_DESCRIPTION;
            }
        )->first();

        $diplomatDescription = $alliance->getSettings()->filter(
            function (AllianceSettingsInterface $setting) {
                return $setting->getSetting() === AllianceSettingsEnum::ALLIANCE_DIPLOMATIC_DESCRIPTION;
            }
        )->first();


        $game->setTemplateVar(
            'FOUNDER_DESCRIPTION',
            $founderDescription !== false ? $founderDescription->getValue() : 'Präsident'
        );

        $game->setTemplateVar(
            'SUCCESSOR_DESCRIPTION',
            $successorDescription !== false ? $successorDescription->getValue() : 'Vize-Präsident'
        );

        $game->setTemplateVar(
            'DIPLOMATIC_DESCRIPTION',
            $diplomatDescription !== false ? $diplomatDescription->getValue() : 'Außenminister'
        );
    }
}
