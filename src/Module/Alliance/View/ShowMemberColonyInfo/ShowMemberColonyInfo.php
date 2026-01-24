<?php

declare(strict_types=1);

namespace Stu\Module\Alliance\View\ShowMemberColonyInfo;

use request;
use Stu\Component\Alliance\Enum\AllianceJobPermissionEnum;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Module\Colony\Component\ColonyComponentEnum;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\StuTime;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ShowMemberColonyInfo implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_MEMBER_COLONY_INFO';

    public function __construct(
        private AllianceJobManagerInterface $allianceJobManager,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private ColonyRepositoryInterface $colonyRepository,
        private ColonyGuiHelperInterface $colonyGuiHelper,
        private StuTime $stuTime
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $colonyId = request::getIntFatal('colonyid');
        $colony = $this->colonyRepository->find($colonyId);

        if ($user->getAlliance() === null) {
            $game->setMacroInAjaxWindow('');
            $game->getInfo()->addInformation(_('Du bist in keiner Allianz'));
            return;
        }
        if ($colony === null) {
            $game->setMacroInAjaxWindow('');
            $game->getInfo()->addInformation(_('Die Kolonie existiert nicht'));
            return;
        }

        if ($user->getAlliance() != $colony->getUser()->getAlliance()) {
            $game->setMacroInAjaxWindow('');
            $game->getInfo()->addInformation(_('Die Kolonie ist nicht in deiner Allianz'));
            return;
        }

        if (!$this->allianceJobManager->hasUserPermission($user, $user->getAlliance(), AllianceJobPermissionEnum::VIEW_COLONIES)) {
            $game->setMacroInAjaxWindow('');
            $game->getInfo()->addInformation(_('Du hast keine Berechtigung, Kolonien anzusehen'));
            return;
        }

        $game->setPageTitle(sprintf(
            _('Kolonie Übersicht der Kolonie %s von %s'),
            $colony->getName(),
            $colony->getUser()->getName()
        ));

        $colonySurface = $this->colonyLibFactory->createColonySurface($colony, null, true);

        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('HOST', $colony);
        $game->setTemplateVar('SURFACE', $colonySurface);
        $game->setTemplateVar('FACTION', $colony->getUser()->getFaction());

        $timestamp = $this->stuTime->time();
        $game->setTemplateVar('COLONY_TIME_HOUR', $colony->getColonyTimeHour($timestamp));
        $game->setTemplateVar('COLONY_TIME_MINUTE', $colony->getColonyTimeMinute($timestamp));
        $game->setTemplateVar('COLONY_DAY_TIME_PREFIX', $colony->getDayTimePrefix($timestamp));
        $game->setTemplateVar('COLONY_DAY_TIME_NAME', $colony->getDayTimeName($timestamp));

        $this->colonyGuiHelper->registerComponents($colony, $game, [
            ColonyComponentEnum::SHIELDING,
            ColonyComponentEnum::EPS_BAR,
            ColonyComponentEnum::STORAGE,
            ColonyComponentEnum::EFFECTS,
            ColonyComponentEnum::SOCIAL
        ]);

        $game->setMacroInAjaxWindow('html/alliance/alliancecolonysurface.twig');
    }
}
