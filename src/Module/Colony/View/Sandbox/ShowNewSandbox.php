<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\Sandbox;

use Override;
use Stu\Module\Control\AccessCheckControllerInterface;
use Stu\Module\Control\AccessGrantedFeatureEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Control\ViewControllerInterface;

final class ShowNewSandbox implements
    ViewControllerInterface,
    AccessCheckControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_NEW_SANDBOX';

    public function __construct(private StuTime $stuTime) {}

    #[Override]
    public function getFeatureIdentifier(): AccessGrantedFeatureEnum
    {
        return AccessGrantedFeatureEnum::COLONY_SANDBOX;
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setPageTitle(_('Neue Sandbox erstellen'));
        $game->setMacroInAjaxWindow('html/colony/sandbox/newSandbox.twig');

        $game->setTemplateVar('SANDBOX_LIST', $game->getUser()->getColonies()->toArray());
        $game->setTemplateVar('SANDBOX_NAME', sprintf('SANDBOX %s', $this->stuTime->date('d M Y')));
    }
}
