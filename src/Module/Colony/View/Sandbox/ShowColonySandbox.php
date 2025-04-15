<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\Sandbox;

use Override;
use request;
use Stu\Component\Colony\ColonyMenuEnum;
use Stu\Lib\Colony\PlanetFieldHostProviderInterface;
use Stu\Lib\Colony\PlanetFieldHostTypeEnum;
use Stu\Module\Colony\Component\ColonyComponentEnum;
use Stu\Module\Colony\Lib\Gui\ColonyGuiHelperInterface;
use Stu\Module\Control\AccessCheckControllerInterface;
use Stu\Module\Control\AccessGrantedFeatureEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewContextTypeEnum;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ColonySandboxRepositoryInterface;

final class ShowColonySandbox implements
    ViewControllerInterface,
    AccessCheckControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_COLONY_SANDBOX';

    public function __construct(
        private ColonySandboxRepositoryInterface $colonySandboxRepository,
        private PlanetFieldHostProviderInterface $planetFieldHostProvider,
        private ColonyGuiHelperInterface $colonyGuiHelper
    ) {}

    #[Override]
    public function getFeatureIdentifier(): AccessGrantedFeatureEnum
    {
        return AccessGrantedFeatureEnum::COLONY_SANDBOX;
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setViewTemplate('html/colony/sandbox/colonySandbox.twig');
        $game->setPageTitle(_('Kolonie-Sandbox'));

        $game->setTemplateVar('SANDBOXES', $this->colonySandboxRepository->getByUser($game->getUser()));

        $sandbox = $game->getViewContext(ViewContextTypeEnum::HOST);
        if ($sandbox === null && request::has('id')) {
            $sandbox = $this->planetFieldHostProvider->loadHostViaRequestParameters($game->getUser(), false);
        }
        $game->appendNavigationPart('?SHOW_COLONY_SANDBOX=1', _('Kolonie-Sandbox'));

        if ($sandbox !== null) {
            $game->appendNavigationPart(
                sprintf(
                    '?%s=1&id=%d&hosttype=%d',
                    self::VIEW_IDENTIFIER,
                    $sandbox->getId(),
                    PlanetFieldHostTypeEnum::SANDBOX->value
                ),
                $sandbox->getName()
            );

            $game->addExecuteJS(sprintf(
                "initializeJsVars(%d, %d, '%s')",
                $sandbox->getId(),
                PlanetFieldHostTypeEnum::SANDBOX->value,
                $game->getSessionString()
            ));

            $menu = ColonyMenuEnum::getFor($game->getViewContext(ViewContextTypeEnum::COLONY_MENU));

            $this->colonyGuiHelper->registerMenuComponents($menu, $sandbox, $game);
            $game->setTemplateVar('SELECTED_COLONY_MENU_TEMPLATE', ColonyMenuEnum::MENU_MAINSCREEN->getTemplate());

            if ($menu === ColonyMenuEnum::MENU_MAINSCREEN) {

                $game->setTemplateVar('SELECTED_COLONY_SUB_MENU_TEMPLATE', ColonyMenuEnum::MENU_INFO->getTemplate());
            } else {

                $game->setTemplateVar('SELECTED_COLONY_SUB_MENU_TEMPLATE', $menu->getTemplate());
                $this->colonyGuiHelper->registerComponents($sandbox, $game, [
                    ColonyComponentEnum::SURFACE,
                    ColonyComponentEnum::SHIELDING,
                    ColonyComponentEnum::EPS_BAR,
                    ColonyComponentEnum::STORAGE
                ]);
            }
        }
    }
}
