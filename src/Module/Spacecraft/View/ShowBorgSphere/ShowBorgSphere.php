<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowBorgSphere;

use Override;
use request;
use Stu\Component\Borg\BorgSpherePhaseEnum;
use Stu\Component\Borg\BorgSphereRaidService;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\StuTime;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\BorgSphereRepositoryInterface;

final class ShowBorgSphere implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_BORG_SPHERE';

    public function __construct(private ShipLoaderInterface $ships, private BorgSphereRepositoryInterface $events, private BorgSphereRaidService $raids, private StuTime $time) {}

    #[Override]
    public function handle(ViewControllerContext $game): void
    {
        $game->setMacroInAjaxWindow('');
        $wrappers = $this->ships->getWrappersBySourceAndUserAndTarget(request::indInt('id'), $game->getUser()->getId(), request::indInt('target'));
        $source = $wrappers->getSource()->get();
        $target = $wrappers->getTarget()?->get();
        if ($target === null || $source->getLocation() !== $target->getLocation()) {
            $game->getInfo()->addInformation('Die Borg-Sphäre befindet sich nicht in diesem Sektor');
            return;
        }
        $event = $this->events->getByShip($target);
        if ($event === null || $event->isFinished() || !$event->getConfiguration()->isActive($this->time->time()) || $target->getCondition()->isDestroyed()) {
            $game->getInfo()->addInformation('Diese Borg-Sphäre ist nicht mehr für eine Außenmission verfügbar');
            return;
        }
        $configuration = $event->getConfiguration();
        $cooldown = $this->raids->getCooldownUntil($game->getUser(), $event);
        $disabled = $event->getPhase() === BorgSpherePhaseEnum::REGENERATING;
        $game->setPageTitle($target->getName());
        $game->setTemplateVar('SHIP', $source);
        $game->setTemplateVar('BORG_SPHERE', $target);
        $game->setTemplateVar('BORG_EVENT', $event);
        $game->setTemplateVar('BORG_DISABLED', $disabled);
        $game->setTemplateVar('BORG_TEAM_SIZE', $this->raids->getTeamSize($source, $event));
        $game->setTemplateVar('BORG_CREW_REQUIREMENT', $this->raids->getCrewRequirementMessage($source));
        $game->setTemplateVar('BORG_SUCCESS', $configuration->getSetting($disabled ? 'raid_success_disabled' : 'raid_success_active'));
        $game->setTemplateVar('BORG_COOLDOWN', $cooldown > $this->time->time() ? $this->time->transformToStuDateTime($cooldown) . ' Uhr' : null);
        $game->setTemplateVar('BORG_NEXT_REGEN', $this->time->transformToStuDateTime($event->getNextTickAt()) . ' Uhr');
        $game->setMacroInAjaxWindow('html/ship/borgSphere.twig');
    }
}
